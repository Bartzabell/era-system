<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\Emergency;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $incidentReports = IncidentReport::with([
            'user:id,first_name,last_name,full_name',
            'barangay:id,barangay_name',
            'incident:id,incident_name,severity_level',
            'emergency:id,emergency_name,severity_level'
        ])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($report) {
            return [
                'id' => $report->id,
                'user_name' => $report->user ? $report->user->full_name : 'N/A',
                'barangay' => $report->barangay ? $report->barangay->barangay_name : 'N/A',
                'incident' => $report->incident ? $report->incident->incident_name : 'N/A',
                'emergency' => $report->emergency ? $report->emergency->emergency_name : 'N/A',
                'severity_level' => $report->severity_level,
                'casualty_count' => $report->casualty_count ?? 0,
                'responder_name' => $report->responder_name,
                'responder_contact_no' => $report->responder_contact_no,
                'plate_no' => $report->plate_no,
                'status' => $report->status,
                'estimated_arrival' => $report->estimated_arrival,
                'datetime_arrived' => $report->datetime_arrived,
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Get all necessary data for the forms
        $barangays = Barangay::select('id', 'barangay_name')
            ->orderBy('barangay_name')
            ->get();

        $incidents = Incident::select('id', 'incident_name', 'severity_level')
            ->orderBy('incident_name')
            ->get();

        $emergencies = Emergency::select('id', 'emergency_name', 'severity_level')
            ->orderBy('emergency_name')
            ->get();

        $users = User::select('id', 'first_name', 'last_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->first_name . ' ' . $user->last_name,
                ];
            });

        return Inertia::render('IncidentReport/Index', [
            'incidentReports' => $incidentReports,
            'barangays' => $barangays,
            'incidents' => $incidents,
            'emergencies' => $emergencies,
            'users' => $users,
        ]);
    }

    public function create()
    {
        // Get all necessary data for the form
        $barangays = Barangay::select('id', 'barangay_name')
            ->orderBy('barangay_name')
            ->get();

        $incidents = Incident::select('id', 'incident_name', 'severity_level')
            ->orderBy('incident_name')
            ->get();

        $emergencies = Emergency::select('id', 'emergency_name', 'severity_level')
            ->orderBy('emergency_name')
            ->get();

        $users = User::select('id', 'first_name', 'last_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->first_name . ' ' . $user->last_name,
                ];
            });

        return Inertia::render('IncidentReport/Create', [
            'barangays' => $barangays,
            'incidents' => $incidents,
            'emergencies' => $emergencies,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barangay_id' => 'required|exists:barangays,id',
            'map_coordinates' => 'nullable|string',
            'emergency_id' => 'required|exists:emergencies,id',
            'incident_id' => 'required|exists:incidents,id',
            'severity_level' => 'required|in:low,medium,high',
            'casualty_count' => 'nullable|integer|min:0',
            'distance' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'responder_name' => 'nullable|string|max:255',
            'responder_contact_no' => 'nullable|string|max:20',
            'estimated_arrival' => 'nullable|date',
            'datetime_arrived' => 'nullable|date',
            'plate_no' => 'nullable|string|max:20',
            'status' => 'required|in:pending,assigned,arrival,completed,cancelled',
            'remarks' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('incident-reports', 'public');
            $validated['attachment'] = $path;
        }

        IncidentReport::create($validated);

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report created successfully.');
    }

    public function show(IncidentReport $incidentReport)
    {
        $incidentReport->load([
            'user:id,first_name,last_name,full_name',
            'barangay:id,barangay_name',
            'incident:id,incident_name,severity_level',
            'emergency:id,emergency_name,severity_level'
        ]);

        return Inertia::render('IncidentReport/Show', [
            'incidentReport' => $incidentReport,
        ]);
    }

    public function edit(IncidentReport $incidentReport)
    {
        // For AJAX request from the edit form
        if (request()->wantsJson()) {
            return response()->json([
                'incidentReport' => $incidentReport,
            ]);
        }

        // For regular page load (if needed)
        $barangays = Barangay::select('id', 'barangay_name')
            ->orderBy('barangay_name')
            ->get();

        $incidents = Incident::select('id', 'incident_name', 'severity_level')
            ->orderBy('incident_name')
            ->get();

        $emergencies = Emergency::select('id', 'emergency_name', 'severity_level')
            ->orderBy('emergency_name')
            ->get();

        $users = User::select('id', 'first_name', 'last_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->first_name . ' ' . $user->last_name,
                ];
            });

        return Inertia::render('IncidentReport/Edit', [
            'incidentReport' => $incidentReport,
            'barangays' => $barangays,
            'incidents' => $incidents,
            'emergencies' => $emergencies,
            'users' => $users,
        ]);
    }

    public function update(Request $request, IncidentReport $incidentReport)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barangay_id' => 'required|exists:barangays,id',
            'map_coordinates' => 'nullable|string',
            'emergency_id' => 'required|exists:emergencies,id',
            'incident_id' => 'required|exists:incidents,id',
            'severity_level' => 'required|in:low,medium,high',
            'casualty_count' => 'nullable|integer|min:0',
            'distance' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'responder_name' => 'nullable|string|max:255',
            'responder_contact_no' => 'nullable|string|max:20',
            'estimated_arrival' => 'nullable|date',
            'datetime_arrived' => 'nullable|date',
            'plate_no' => 'nullable|string|max:20',
            'status' => 'required|in:pending,assigned,arrival,completed,cancelled',
            'remarks' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($incidentReport->attachment) {
                Storage::disk('public')->delete($incidentReport->attachment);
            }

            $path = $request->file('attachment')->store('incident-reports', 'public');
            $validated['attachment'] = $path;
        }

        $incidentReport->update($validated);

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report updated successfully.');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        // Delete attachment if exists
        if ($incidentReport->attachment) {
            Storage::disk('public')->delete($incidentReport->attachment);
        }

        $incidentReport->delete();

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report deleted successfully.');
    }
}
