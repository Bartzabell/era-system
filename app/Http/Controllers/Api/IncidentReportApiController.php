<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\Emergency;
use App\Models\Incident;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IncidentReportApiController extends Controller
{
    // Get dropdowns data
    public function getDropdowns()
    {
        $emergencies = Emergency::select('id', 'emergency_name', 'severity_level')->get();
        $incidents = Incident::select('id', 'incident_name', 'severity_level')->get();
        $barangays = Barangay::select('id', 'barangay_name', 'landmark')->get();

        return response()->json([
            'success' => true,
            'emergencies' => $emergencies,
            'incidents' => $incidents,
            'barangays' => $barangays
        ]);
    }

    // Create incident report
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emergency_id' => 'required|exists:emergencies,id',
            'incident_id' => 'required|exists:incidents,id',
            'barangay_id' => 'nullable|exists:barangays,id',
            'casualty_count' => 'required|integer|min:0',
            'map_coordinates' => 'nullable|string',
            'attachment' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $report = IncidentReport::create([
            'user_id' => $request->user()->id,
            'emergency_id' => $request->emergency_id,
            'incident_id' => $request->incident_id,
            'barangay_id' => $request->barangay_id,
            'casualty_count' => $request->casualty_count,
            'map_coordinates' => $request->map_coordinates,
            'attachment' => $request->attachment,
            'severity_level' => 'low',
            'status' => 'pending',
            'distance' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Incident report submitted successfully',
            'report' => $report
        ], 201);
    }

    // Get user's incident reports
    public function index(Request $request)
    {
        $reports = IncidentReport::where('user_id', $request->user()->id)
            ->with(['emergency', 'incident', 'barangay'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }
}