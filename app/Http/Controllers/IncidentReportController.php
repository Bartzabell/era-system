<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\Emergency;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $user          = Auth::user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $incidentReport = IncidentReport::with([
                'user',
                'barangay',
                'emergency',
            ])->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('barangay', function ($q) use ($search) {
                        $q->where('barangay_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('emergency', function ($q) use ($search) {
                        $q->where('emergency_name', 'like', "%{$search}%");
                    });
                });
            });

        if (!$hasFullAccess) {
            $incidentReport->where('user_id', $user->id);
        }

        $barangays = Barangay::select('id', 'barangay_name')->orderBy('barangay_name')->get();

        $incidents = Incident::select('id', 'incident_name', 'emergency_id', 'base_severity', 'base_time', 'base_resources', 'base_secondary')
            ->orderBy('incident_name')->get();

        $emergencies = Emergency::select('id', 'emergency_name')->orderBy('emergency_name')->get();

        $users = User::select('id', 'first_name', 'last_name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'full_name' => $u->first_name . ' ' . $u->last_name]);

        return Inertia::render('IncidentReport/Index', [
            'incidentReports' => $incidentReport->latest()->paginate($perPage)->withQueryString(),
            'barangays'       => $barangays,
            'incidents'       => $incidents,
            'emergencies'     => $emergencies,
            'users'           => $users,
            'hasFullAccess'   => $hasFullAccess,
            'filters'         => $request->only('per_page', 'search'),
            'currentUserId'   => Auth::id(),
        ]);
    }

    public function store(Request $request)
    {
        $user          = Auth::user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        $rules = [
            'barangay_id'            => 'required|exists:barangays,id',
            'map_coordinates'        => 'required|string',
            'emergency_id'           => 'required|exists:emergencies,id',
            'incident_id'            => 'required|exists:incidents,id',
            'casualty_count'         => 'nullable|integer|min:0',
            'minor_casualty_count'   => 'nullable|integer|min:0',
            'serious_casualty_count' => 'nullable|integer|min:0',
            'deceased_casualty_count'=> 'nullable|integer|min:0',
            'distance'               => 'nullable|numeric|min:0',
            'attachment'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'remarks'                => 'nullable|string',
            'priority_score'         => 'nullable|numeric|min:0|max:10',
            'priority_level'         => 'nullable|string|max:10',
            'priority_label'         => 'nullable|string|max:50',
        ];

        if ($hasFullAccess) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $hasFullAccess) {
            $data = $request->only([
                'barangay_id', 'map_coordinates', 'emergency_id', 'incident_id',
                'casualty_count', 'minor_casualty_count', 'serious_casualty_count', 'deceased_casualty_count',
                'distance', 'remarks',
                'priority_score', 'priority_level', 'priority_label',
            ]);

            $incident = Incident::find($request->incident_id);
            $data['severity_level'] = match(true) {
                ($incident?->base_severity ?? 0) >= 7 => 'high',
                ($incident?->base_severity ?? 0) >= 4 => 'medium',
                default                                => 'low',
            };

            $data['user_id']    = $hasFullAccess ? $request->user_id : Auth::id();
            $data['status']     = 'waiting';
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('incident-reports', 'public');
            }

            IncidentReport::create($data);
        });

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report created successfully.');
    }

   public function update(Request $request, IncidentReport $incidentReport)
    {
        $user = Auth::user();
        if (!$user->hasPermission('admin_access') && !$user->hasPermission('responder_access')) {
            abort(403, 'Unauthorized to update incident reports.');
        }

        $request->validate([
            'distance'               => 'nullable|numeric|min:0',
            'attachment'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'responder_name'         => 'nullable|string|max:255',
            'responder_contact_no'   => 'nullable|string|max:20',
            'estimated_arrival'      => 'nullable|date',
            'datetime_arrived'       => 'nullable|date',
            'plate_no'               => 'nullable|string|max:20',
            'status'                 => 'required|in:waiting,assigned,arriving,resolved,cancelled',
            'remarks'                => 'nullable|string',
            'priority_score'         => 'nullable|numeric|min:0|max:10',
            'priority_level'         => 'nullable|string|max:10',
            'priority_label'         => 'nullable|string|max:50',
            'minor_casualty_count'   => 'nullable|integer|min:0',
            'serious_casualty_count' => 'nullable|integer|min:0',
            'deceased_casualty_count'=> 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $incidentReport) {
            $data = $request->only([
                'distance', 'responder_name', 'responder_contact_no',
                'estimated_arrival', 'datetime_arrived', 'plate_no',
                'status', 'remarks',
                'priority_score', 'priority_level', 'priority_label',
                'minor_casualty_count', 'serious_casualty_count', 'deceased_casualty_count',
            ]);

            $data['casualty_count'] = ($data['minor_casualty_count'] ?? 0)
                                    + ($data['serious_casualty_count'] ?? 0)
                                    + ($data['deceased_casualty_count'] ?? 0);

            $data['updated_by'] = Auth::id();

            if ($request->hasFile('attachment')) {
                if ($incidentReport->attachment) {
                    Storage::disk('public')->delete($incidentReport->attachment);
                }
                $data['attachment'] = $request->file('attachment')->store('incident-reports', 'public');
            }

            $incidentReport->update($data);
        });

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report updated successfully.');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        $user = Auth::user();
        if (!$user->hasPermission('admin_access') && !$user->hasPermission('responder_access')) {
            abort(403, 'Unauthorized to delete incident reports.');
        }

        DB::transaction(function () use ($incidentReport) {
            if ($incidentReport->attachment) {
                Storage::disk('public')->delete($incidentReport->attachment);
            }
            $incidentReport->update(['deleted_by' => Auth::id()]);
            $incidentReport->delete();
        });

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report deleted successfully.');
    }

    public function show(IncidentReport $incidentReport)
    {
        $user          = Auth::user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        if (!$hasFullAccess && $incidentReport->user_id !== $user->id) {
            abort(403, 'Unauthorized to view this incident report.');
        }

        $incidentReport->load([
            'user:id,first_name,last_name,full_name',
            'barangay:id,barangay_name',
            'incident:id,incident_name',
            'emergency:id,emergency_name',
        ]);

        return Inertia::render('IncidentReport/Show', [
            'incidentReport' => $incidentReport,
        ]);
    }
}
