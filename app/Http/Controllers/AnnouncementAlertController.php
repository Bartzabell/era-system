<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AnnouncementAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnouncementAlertController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $announcements = AnnouncementAlert::with('creator')
            ->latest()
            ->paginate($perPage);

        $broadcastAlerts = AnnouncementAlert::with('creator')
            ->whereDate('created_at', Carbon::today())
            ->whereNull('deleted_at')
            ->latest()
            ->get()
            ->map(fn($a) => $this->formatAnnouncement($a));

        $broadcastHistory = AnnouncementAlert::with('creator')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::today()->subSecond(),
            ])
            ->whereNull('deleted_at')
            ->latest()
            ->get()
            ->map(fn($a) => $this->formatAnnouncement($a));

        return Inertia::render('AnnouncementAlert/Index', [
            'announcements'    => $announcements,
            'broadcastAlerts'  => $broadcastAlerts,
            'broadcastHistory' => $broadcastHistory,
            'filters'          => $request->only('per_page'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'announcement_title'   => 'required|string|max:255',
            'announcement_message' => 'required|string',
            'for_citizens'         => 'boolean',
            'for_responders'       => 'boolean',
            'for_administrators'   => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            AnnouncementAlert::create([
                'announcement_title'   => $request->announcement_title,
                'announcement_message' => $request->announcement_message,
                'for_citizens'         => $request->boolean('for_citizens'),
                'for_responders'       => $request->boolean('for_responders'),
                'for_administrators'   => $request->boolean('for_administrators'),
                'created_by'           => Auth::id(),
                'updated_by'           => Auth::id(),
            ]);
        });

        return redirect()->route('announcement-alert.index')
            ->with('success', 'Announcement broadcast successfully.');
    }

    public function destroy(AnnouncementAlert $announcementAlert)
    {
        DB::transaction(function () use ($announcementAlert) {
            $announcementAlert->update(['deleted_by' => Auth::id()]);
            $announcementAlert->delete();
        });

        return redirect()->route('announcement-alert.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    private function formatAnnouncement(AnnouncementAlert $a): array
    {
        $audience = [];
        if ($a->for_citizens)       $audience[] = 'Citizens';
        if ($a->for_responders)     $audience[] = 'Responders';
        if ($a->for_administrators) $audience[] = 'Administrators';

        return [
            'id'                   => $a->id,
            'incident_report_id'   => $a->incident_report_id,
            'announcement_title'   => $a->announcement_title,
            'announcement_message' => $a->announcement_message,
            'for_citizens'         => $a->for_citizens,
            'for_responders'       => $a->for_responders,
            'for_administrators'   => $a->for_administrators,
            'audience'             => implode(' & ', $audience) ?: 'None',
            'created_by_name'      => $a->creator?->full_name ?? '',
            'time_ago'             => Carbon::parse($a->created_at)->diffForHumans(),
            'created_at'           => $a->created_at->format('M d, Y h:i A'),
            'is_incident_alert'    => !is_null($a->incident_report_id),
        ];
    }

    /**
     * Returns notifications for the authenticated user based on their role.
     *
     * Role visibility matrix:
     *  - citizen       → for_citizens = true
     *  - responder     → for_responders = true  (includes incident alerts)
     *  - administrator → for_administrators = true OR for_responders = true
     *                    (admins see everything targeted at responders too)
     *  - (other/admin) → all alerts (legacy fallback)
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');

        $query = AnnouncementAlert::whereNull('deleted_at')
            ->latest()
            ->limit(20);

        if ($role === 'citizen') {
            $query->where('for_citizens', true);

        } elseif ($role === 'responder') {
            // Responders see: manual alerts addressed to them + auto incident alerts
            $query->where('for_responders', true);

        } elseif ($role === 'administrator' || $role === 'assistant admin') {
            // Administrators see: alerts addressed to them + alerts addressed to responders
            $query->where(function ($q) {
                $q->where('for_administrators', true)
                  ->orWhere('for_responders', true);
            });
        }
        // Any other role (super-admin, etc.) sees everything — no additional filter

        $readIds = DB::table('announcement_alert_reads')
            ->where('user_id', Auth::id())
            ->pluck('announcement_alert_id')
            ->toArray();

        $notifications = $query->get()->map(function ($a) use ($readIds) {
            return [
                'id'                   => $a->id,
                'incident_report_id'   => $a->incident_report_id,
                'announcement_title'   => $a->announcement_title,
                'announcement_message' => $a->announcement_message,
                'for_citizens'         => $a->for_citizens,
                'for_responders'       => $a->for_responders,
                'for_administrators'   => $a->for_administrators,
                'time_ago'             => Carbon::parse($a->created_at)->diffForHumans(),
                'is_read'              => in_array($a->id, $readIds),
                'is_incident_alert'    => !is_null($a->incident_report_id),
            ];
        });

        $unreadCount = $notifications->filter(fn($n) => !$n['is_read'])->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'announcement_id' => 'required|exists:announcement_alerts,id',
        ]);

        DB::table('announcement_alert_reads')->insertOrIgnore([
            'announcement_alert_id' => $request->announcement_id,
            'user_id'               => Auth::id(),
            'read_at'               => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
