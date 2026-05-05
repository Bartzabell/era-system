<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\Emergency;
use App\Models\IrResponder;
use App\Models\Responder;
use App\Models\SiteLocation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DispatchController extends Controller
{
    private function hasFullAccess(): bool
    {
        return Auth::user()->hasPermission('admin_access') || Auth::user()->hasPermission('responder_access');
    }

    private function sharedData(): array
    {
        return [
            'barangays'     => Barangay::select('id', 'barangay_name')->orderBy('barangay_name')->get(),
            'incidents'     => Incident::select('id', 'incident_name', 'emergency_id', 'base_severity', 'base_time', 'base_resources', 'base_secondary')->orderBy('incident_name')->get(),
            'emergencies'   => Emergency::select('id', 'emergency_name')->orderBy('emergency_name')->get(),
            'siteLocations' => SiteLocation::select('id', 'site_name', 'site_type', 'coordinates')->orderBy('site_name')->get(),
            'users' => Responder::with('user')->where('is_active', true)->get()
                ->filter(fn($r) => $r->user !== null)
                ->map(fn($r) => [
                    'id'        => $r->user?->id,
                    'full_name' => trim(($r->user?->first_name ?? '') . ' ' . ($r->user?->last_name ?? '')),
                ])
                ->values(),
        ];
    }

    public function index(Request $request)
    {
        $query = IncidentReport::with([
            'user:id,first_name,last_name,mobile_no',
            'barangay:id,barangay_name',
            'incident:id,incident_name',
            'emergency:id,emergency_name',
            'siteLocation:id,site_name',
            'irResponders',
        ])->orderBy('reported_at', 'desc')->orderBy('created_at', 'desc');

        if (!$this->hasFullAccess()) $query->where('user_id', Auth::id());

        $reports = $query->get()->map(function ($r) {
            $r->attachments = $r->incident_code
                ? Storage::disk('public')->files('incident-attachments/' . $r->incident_code)
                : [];
            return $r;
        });

        return Inertia::render('Dispatch/Index', [
            ...$this->sharedData(),
            'incidentReports' => $reports,
            'hasFullAccess'   => $this->hasFullAccess(),
            'currentUserId'   => Auth::id(),
        ]);
    }

    public function store(Request $request)
    {
        $full  = $this->hasFullAccess();
        $rules = [
            'barangay_id'             => 'required|exists:barangays,id',
            'map_coordinates'         => 'required|string',
            'emergency_id'            => 'required|exists:emergencies,id',
            'incident_id'             => 'required|exists:incidents,id',
            'casualty_count'          => 'nullable|integer|min:0',
            'minor_casualty_count'    => 'nullable|integer|min:0',
            'serious_casualty_count'  => 'nullable|integer|min:0',
            'deceased_casualty_count' => 'nullable|integer|min:0',
            'distance'                => 'nullable|numeric|min:0',
            'distance_km'             => 'nullable|numeric|min:0',
            'attachment'              => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'remarks'                 => 'nullable|string',
            'priority_score'          => 'nullable|numeric|min:0|max:10',
            'priority_level'          => 'nullable|string|max:10',
            'priority_label'          => 'nullable|string|max:50',
            'reported_at'             => 'nullable|date',
            'site_location_id'        => 'nullable|exists:site_locations,id',
        ];

        if ($full) $rules += [
            'user_id'              => 'required|exists:users,id',
            'responder_name'       => 'nullable|string|max:255',
            'responder_contact_no' => 'nullable|string|max:20',
            'responder_count'      => 'nullable|integer|min:0',
            'estimated_arrival'    => 'nullable|date',
            'datetime_arrived'     => 'nullable|date',
            'plate_no'             => 'nullable|string|max:20',
            'status'               => 'nullable|in:waiting,assigned,arriving,resolved,cancelled',
            'responder_remarks'    => 'nullable|string',
            'treatment_provided'   => 'nullable|string',
            'cancel_remarks'       => 'nullable|string',
            'responder_attachment' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        ];

        $request->validate($rules);

        DB::transaction(function () use ($request, $full) {
            $data = $request->only([
                'barangay_id', 'map_coordinates', 'emergency_id', 'incident_id',
                'casualty_count', 'minor_casualty_count', 'serious_casualty_count', 'deceased_casualty_count',
                'distance', 'remarks', 'priority_score', 'priority_level', 'priority_label',
                'reported_at', 'site_location_id',
            ]);

            $data['distance'] = $request->distance_km ?? $request->distance;

            if ($full) {
                $data += $request->only(['responder_name', 'responder_contact_no', 'estimated_arrival', 'datetime_arrived', 'plate_no', 'responder_count', 'responder_remarks', 'treatment_provided', 'cancel_remarks']);
                $data['user_id'] = $request->user_id;
                $data['status']  = $request->status ?? 'waiting';
            } else {
                $data['user_id'] = Auth::id();
                $data['status']  = 'waiting';
            }

            $incident = Incident::find($request->incident_id);
            $data['severity_level'] = match(true) {
                ($incident?->base_severity ?? 0) >= 7 => 'high',
                ($incident?->base_severity ?? 0) >= 4 => 'medium',
                default => 'low',
            };
            $data['created_by'] = $data['updated_by'] = Auth::id();

            $report = IncidentReport::create($data);
            $incidentCode = 'IND-' . now()->year . '-' . str_pad($report->id, 4, '0', STR_PAD_LEFT);
            $report->incident_code = $incidentCode;

            $folder = 'incident-attachments/' . $incidentCode;

            if ($request->hasFile('attachment'))
                $report->attachment = $request->file('attachment')->store($folder, 'public');
            if ($request->hasFile('responder_attachment'))
                $report->responder_attachment = $request->file('responder_attachment')->store($folder, 'public');

            $report->save();

        });

        return redirect()->route('dispatch.index')->with('success', 'Incident report created successfully.');
    }

    public function update(Request $request, IncidentReport $incidentReport)
    {
        if (!$this->hasFullAccess()) abort(403);

        $request->validate([
            'emergency_id'            => 'nullable|exists:emergencies,id',
            'incident_id'             => 'nullable|exists:incidents,id',
            'distance'                => 'nullable|numeric|min:0',
            'distance_km'             => 'nullable|numeric|min:0',
            'attachment'              => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'responder_attachment'    => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'responder_name'          => 'nullable|string|max:255',
            'responder_contact_no'    => 'nullable|string|max:20',
            'responder_count'         => 'nullable|integer|min:0',
            'estimated_arrival'       => 'nullable|date',
            'datetime_arrived'        => 'nullable|date',
            'plate_no'                => 'nullable|string|max:20',
            'status'                  => 'required|in:waiting,assigned,arriving,resolved,cancelled',
            'remarks'                 => 'nullable|string',
            'responder_remarks'       => 'nullable|string',
            'treatment_provided'      => 'nullable|string',
            'cancel_remarks'          => 'nullable|string',
            'priority_score'          => 'nullable|numeric|min:0|max:10',
            'priority_level'          => 'nullable|string|max:10',
            'priority_label'          => 'nullable|string|max:50',
            'minor_casualty_count'    => 'nullable|numeric|min:0',
            'serious_casualty_count'  => 'nullable|numeric|min:0',
            'deceased_casualty_count' => 'nullable|numeric|min:0',
            'reported_at'             => 'nullable|date',
            'site_location_id'        => 'nullable|exists:site_locations,id',
        ]);

        DB::transaction(function () use ($request, $incidentReport) {
            $data = $request->only([
                'distance', 'responder_name', 'responder_contact_no', 'responder_count',
                'estimated_arrival', 'datetime_arrived', 'plate_no', 'status',
                'remarks', 'responder_remarks', 'treatment_provided', 'cancel_remarks',
                'priority_score', 'priority_level', 'priority_label',
                'minor_casualty_count', 'serious_casualty_count', 'deceased_casualty_count',
                'reported_at', 'site_location_id', 'emergency_id', 'incident_id',
            ]);

            if ($request->filled('incident_id')) {
                $incident = Incident::find($request->incident_id);
                $data['severity_level'] = match(true) {
                    ($incident?->base_severity ?? 0) >= 7 => 'high',
                    ($incident?->base_severity ?? 0) >= 4 => 'medium',
                    default => 'low',
                };
            }

            $data['distance']       = $request->distance_km ?? $request->distance ?? $incidentReport->distance;
            $data['casualty_count'] = ($data['minor_casualty_count'] ?? 0) + ($data['serious_casualty_count'] ?? 0) + ($data['deceased_casualty_count'] ?? 0);
            $data['updated_by']     = Auth::id();

            $folder = 'incident-attachments/' . $incidentReport->incident_code;
                foreach (['attachment', 'responder_attachment'] as $field) {
                    if ($request->hasFile($field)) {
                        if ($incidentReport->$field) Storage::disk('public')->delete($incidentReport->$field);
                        $data[$field] = $request->file($field)->store($folder, 'public');
                    }
                }

            $incidentReport->update($data);
        });

        return redirect()->route('dispatch.index')->with('success', 'Incident report updated successfully.');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        if (!$this->hasFullAccess()) abort(403);

        DB::transaction(function () use ($incidentReport) {
            foreach (['attachment', 'responder_attachment'] as $field)
                if ($incidentReport->$field) Storage::disk('public')->delete($incidentReport->$field);
            $incidentReport->update(['deleted_by' => Auth::id()]);
            $incidentReport->delete();
        });

        return redirect()->route('dispatch.index')->with('success', 'Incident report deleted successfully.');
    }

    public function assignTeam(Request $request, IncidentReport $incidentReport)
    {
        if (!$this->hasFullAccess()) abort(403);

        $request->validate([
            'leader_id'    => 'required|exists:users,id',
            'member_ids'   => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        DB::transaction(function () use ($request, $incidentReport) {
            // forceDelete to avoid soft-delete collision on re-assign
            $incidentReport->irResponders()->forceDelete();

            $leader = User::find($request->leader_id);
            $allIds = array_unique(array_merge([$request->leader_id], $request->member_ids ?? []));

            IrResponder::create([
                'ir_id' => $incidentReport->id, 'responder_id' => $leader->id,
                'responder_name' => $leader->first_name . ' ' . $leader->last_name,
                'responder_type' => 'leader', 'created_by' => Auth::id(), 'updated_by' => Auth::id(),
            ]);

            foreach ($request->member_ids ?? [] as $uid) {
                $u = User::find($uid);
                IrResponder::create([
                    'ir_id' => $incidentReport->id, 'responder_id' => $uid,
                    'responder_name' => $u->first_name . ' ' . $u->last_name,
                    'responder_type' => 'member', 'created_by' => Auth::id(), 'updated_by' => Auth::id(),
                ]);
            }

            $incidentReport->update([
                'responder_name' => $leader->first_name . ' ' . $leader->last_name,
                'responder_contact_no' => $leader->mobile_no,
                'status'         => 'assigned',
                'updated_by'     => Auth::id(),
            ]);

            $alert = AnnouncementAlert::create([
                'announcement_title'   => 'Team Assignment: ' . $incidentReport->incident_code,
                'announcement_message' => 'You have been assigned to incident ' . $incidentReport->incident_code
                    . ' at ' . ($incidentReport->barangay?->barangay_name ?? $incidentReport->map_coordinates)
                    . '. Team leader: ' . $leader->first_name . ' ' . $leader->last_name . '.',
                'for_responders' => true, 'for_citizens' => false,
                'created_by' => Auth::id(), 'updated_by' => Auth::id(),
            ]);

            DB::table('announcement_alert_reads')->insert(
                collect($allIds)->map(fn($uid) => ['announcement_alert_id' => $alert->id, 'user_id' => $uid])->toArray()
            );
        });

        return redirect()->route('dispatch.index')->with('success', 'Team assigned.');
    }
}
