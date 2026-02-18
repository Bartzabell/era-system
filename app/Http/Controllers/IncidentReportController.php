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
        $user = auth()->user();

        // Check if user has admin or responder access
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        $query = IncidentReport::with([
            'user:id,first_name,last_name,full_name',
            'barangay:id,barangay_name',
            'incident:id,incident_name,severity_level',
            'emergency:id,emergency_name,severity_level'
        ]);

        // If user doesn't have full access, only show their own reports
        if (!$hasFullAccess) {
            $query->where('user_id', $user->id);
        }

        $incidentReports = $query->orderBy('created_at', 'desc')
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
            'hasFullAccess' => $hasFullAccess,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barangay_id' => 'required|exists:barangays,id',
            'map_coordinates' => 'required|string', // Now required
            'emergency_id' => 'required|exists:emergencies,id',
            'incident_id' => 'required|exists:incidents,id',
            'severity_level' => 'required|in:low,medium,high',
            'casualty_count' => 'nullable|integer|min:0',
            'distance' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'remarks' => 'nullable|string',
        ]);

        // Auto-fill user_id from authenticated user
        $validated['user_id'] = auth()->id();

        // Auto-set status to pending
        $validated['status'] = 'pending';

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('incident-reports', 'public');
            $validated['attachment'] = $path;
        }

        IncidentReport::create($validated);

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report created successfully.');
    }

    // In the create() method, remove users from the data being passed:

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

        // Removed users query - not needed for create form

        return Inertia::render('IncidentReport/Create', [
            'barangays' => $barangays,
            'incidents' => $incidents,
            'emergencies' => $emergencies,
            // 'users' => $users, // Removed
        ]);
    }

    public function show(IncidentReport $incidentReport)
    {
        $user = auth()->user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        // Check if user can view this report
        if (!$hasFullAccess && $incidentReport->user_id !== $user->id) {
            abort(403, 'Unauthorized to view this incident report.');
        }

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
        $user = auth()->user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        // Only users with full access can edit
        if (!$hasFullAccess) {
            abort(403, 'Unauthorized to edit incident reports.');
        }

        if (request()->wantsJson()) {
            $incidentReport->load([
                'user:id,first_name,last_name',
                'barangay:id,barangay_name',
                'incident:id,incident_name,severity_level',
                'emergency:id,emergency_name,severity_level'
            ]);

            return response()->json([
                'incidentReport' => [
                    'id' => $incidentReport->id,
                    'user_id' => $incidentReport->user_id,
                    'user_name' => $incidentReport->user ? $incidentReport->user->first_name . ' ' . $incidentReport->user->last_name : 'N/A',
                    'barangay_id' => $incidentReport->barangay_id,
                    'barangay_name' => $incidentReport->barangay ? $incidentReport->barangay->barangay_name : 'N/A',
                    'map_coordinates' => $incidentReport->map_coordinates,
                    'emergency_id' => $incidentReport->emergency_id,
                    'emergency_name' => $incidentReport->emergency ? $incidentReport->emergency->emergency_name : 'N/A',
                    'incident_id' => $incidentReport->incident_id,
                    'incident_name' => $incidentReport->incident ? $incidentReport->incident->incident_name : 'N/A',
                    'severity_level' => $incidentReport->severity_level,
                    'casualty_count' => $incidentReport->casualty_count,
                    'distance' => $incidentReport->distance,
                    'responder_name' => $incidentReport->responder_name,
                    'responder_contact_no' => $incidentReport->responder_contact_no,
                    'estimated_arrival' => $incidentReport->estimated_arrival,
                    'datetime_arrived' => $incidentReport->datetime_arrived,
                    'plate_no' => $incidentReport->plate_no,
                    'status' => $incidentReport->status,
                    'remarks' => $incidentReport->remarks,
                    'attachment' => $incidentReport->attachment,
                ],
            ]);
        }

        return Inertia::render('IncidentReport/Edit', [
            'incidentReport' => $incidentReport,
        ]);
    }

    public function update(Request $request, IncidentReport $incidentReport)
    {
        $user = auth()->user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        // Only users with full access can update
        if (!$hasFullAccess) {
            abort(403, 'Unauthorized to update incident reports.');
        }

        $validated = $request->validate([
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

        if ($request->hasFile('attachment')) {
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
        $user = auth()->user();
        $hasFullAccess = $user->hasPermission('admin_access') || $user->hasPermission('responder_access');

        // Only users with full access can delete
        if (!$hasFullAccess) {
            abort(403, 'Unauthorized to delete incident reports.');
        }

        if ($incidentReport->attachment) {
            Storage::disk('public')->delete($incidentReport->attachment);
        }

        $incidentReport->delete();

        return redirect()->route('incident-report.index')
            ->with('success', 'Incident report deleted successfully.');
    }
}
