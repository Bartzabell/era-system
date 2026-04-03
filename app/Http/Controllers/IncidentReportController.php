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
    private function hasFullAccess(): bool
    {
        $user = Auth::user();
        return $user->hasPermission('admin_access') || $user->hasPermission('responder_access');
    }

    private function sharedData(): array
    {
        return [
            'barangays'  => Barangay::select('id', 'barangay_name')->orderBy('barangay_name')->get(),
            'incidents'  => Incident::select('id', 'incident_name', 'emergency_id', 'base_severity', 'base_time', 'base_resources', 'base_secondary')->orderBy('incident_name')->get(),
            'emergencies'=> Emergency::select('id', 'emergency_name')->orderBy('emergency_name')->get(),
            'users'      => User::get()->map(fn($u) => ['id' => $u->id, 'full_name' => $u->first_name . ' ' . $u->last_name]),
        ];
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = IncidentReport::with(['user', 'barangay', 'emergency'])
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->whereHas('user',      fn($q) => $q->where('full_name',       'like', "%{$search}%"))
                  ->orWhereHas('barangay', fn($q) => $q->where('barangay_name',  'like', "%{$search}%"))
                  ->orWhereHas('emergency',fn($q) => $q->where('emergency_name', 'like', "%{$search}%"))
            ));

        if (!$this->hasFullAccess()) {
            $query->where('user_id', Auth::id());
        }

        return Inertia::render('IncidentReport/Index', [
            ...$this->sharedData(),
            'incidentReports' => $query->latest()->paginate($perPage)->withQueryString(),
            'hasFullAccess'   => $this->hasFullAccess(),
            'filters'         => $request->only('per_page', 'search'),
            'currentUserId'   => Auth::id(),
        ]);
    }

    public function store(Request $request)
    {
        $full = $this->hasFullAccess();

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
            'attachment'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'remarks'                 => 'nullable|string',
            'priority_score'          => 'nullable|numeric|min:0|max:10',
            'priority_level'          => 'nullable|string|max:10',
            'priority_label'          => 'nullable|string|max:50',
            'reported_at'             => 'nullable|date',
        ];

        if ($full) {
            $rules['user_id']              = 'required|exists:users,id';
            $rules['responder_name']       = 'nullable|string|max:255';
            $rules['responder_contact_no'] = 'nullable|string|max:20';
            $rules['estimated_arrival']    = 'nullable|date';
            $rules['datetime_arrived']     = 'nullable|date';
            $rules['plate_no']             = 'nullable|string|max:20';
            $rules['responder_count']      = 'nullable|integer|min:0';
            $rules['status']               = 'nullable|in:waiting,assigned,arriving,resolved,cancelled';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $full) {
            $data = $request->only([
                'barangay_id', 'map_coordinates', 'emergency_id', 'incident_id',
                'casualty_count', 'minor_casualty_count', 'serious_casualty_count', 'deceased_casualty_count',
                'distance', 'remarks', 'priority_score', 'priority_level', 'priority_label', 'reported_at',
            ]);

            if ($full) {
                $data = array_merge($data, $request->only([
                    'responder_name', 'responder_contact_no', 'estimated_arrival',
                    'datetime_arrived', 'plate_no', 'responder_count',
                ]));
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
                default                                => 'low',
            };

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('incident-reports', 'public');
            }

            $report = IncidentReport::create($data);

            // Auto-generate incident_code: IND#-YYYY-00ID
            $report->incident_code = 'IND#-' . now()->year . '-' . str_pad($report->id, 4, '0', STR_PAD_LEFT);
            $report->save();
        });

        return redirect()->route('incident-report.index')->with('success', 'Incident report created successfully.');
    }

    public function update(Request $request, IncidentReport $incidentReport)
    {
        if (!$this->hasFullAccess()) abort(403, 'Unauthorized.');

        $request->validate([
            'distance'                => 'nullable|numeric|min:0',
            'attachment'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'responder_name'          => 'nullable|string|max:255',
            'responder_contact_no'    => 'nullable|string|max:20',
            'responder_count'         => 'nullable|integer|min:0',
            'estimated_arrival'       => 'nullable|date',
            'datetime_arrived'        => 'nullable|date',
            'plate_no'                => 'nullable|string|max:20',
            'status'                  => 'required|in:waiting,assigned,arriving,resolved,cancelled',
            'remarks'                 => 'nullable|string',
            'priority_score'          => 'nullable|numeric|min:0|max:10',
            'priority_level'          => 'nullable|string|max:10',
            'priority_label'          => 'nullable|string|max:50',
            'minor_casualty_count'    => 'nullable|integer|min:0',
            'serious_casualty_count'  => 'nullable|integer|min:0',
            'deceased_casualty_count' => 'nullable|integer|min:0',
            'reported_at'             => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $incidentReport) {
            $data = $request->only([
                'distance', 'responder_name', 'responder_contact_no', 'responder_count',
                'estimated_arrival', 'datetime_arrived', 'plate_no', 'status', 'remarks',
                'priority_score', 'priority_level', 'priority_label',
                'minor_casualty_count', 'serious_casualty_count', 'deceased_casualty_count', 'reported_at',
            ]);

            $data['casualty_count'] = ($data['minor_casualty_count'] ?? 0)
                                    + ($data['serious_casualty_count'] ?? 0)
                                    + ($data['deceased_casualty_count'] ?? 0);
            $data['updated_by'] = Auth::id();

            if ($request->hasFile('attachment')) {
                if ($incidentReport->attachment) Storage::disk('public')->delete($incidentReport->attachment);
                $data['attachment'] = $request->file('attachment')->store('incident-reports', 'public');
            }

            $incidentReport->update($data);
        });

        return redirect()->route('incident-report.index')->with('success', 'Incident report updated successfully.');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        if (!$this->hasFullAccess()) abort(403, 'Unauthorized.');

        DB::transaction(function () use ($incidentReport) {
            if ($incidentReport->attachment) Storage::disk('public')->delete($incidentReport->attachment);
            $incidentReport->update(['deleted_by' => Auth::id()]);
            $incidentReport->delete();
        });

        return redirect()->route('incident-report.index')->with('success', 'Incident report deleted successfully.');
    }

    public function show(IncidentReport $incidentReport)
    {
        if (!$this->hasFullAccess() && $incidentReport->user_id !== Auth::id()) abort(403, 'Unauthorized.');

        $incidentReport->load(['user:id,first_name,last_name,full_name', 'barangay:id,barangay_name', 'incident:id,incident_name', 'emergency:id,emergency_name']);

        return Inertia::render('IncidentReport/Show', ['incidentReport' => $incidentReport]);
    }
}
