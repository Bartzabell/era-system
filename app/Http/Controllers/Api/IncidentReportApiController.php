<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\Emergency;
use App\Models\Incident;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

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
            'remarks' => 'nullable|string',
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
            'status' => 'waiting',
            'distance' => null,
            'remarks' => $request->remarks,
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

    // Get all pending reports (waiting status)
    public function getPendingReports(Request $request)
    {
        $reports = IncidentReport::where('status', 'waiting')
            ->with(['emergency', 'incident', 'barangay', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }

    // Accept a report
    public function acceptReport(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'plate_no' => 'nullable|string',
            'responder_latitude' => 'required|numeric',
            'responder_longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if responder already has an ongoing case
        $ongoingCase = IncidentReport::where('responder_name', $request->user()->name)
            ->whereIn('status', ['assigned', 'arriving'])
            ->first();

        if ($ongoingCase) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an ongoing case. Please resolve it first.'
            ], 400);
        }

        $report = IncidentReport::findOrFail($id);

        if ($report->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'This report has already been accepted.'
            ], 400);
        }

        // Parse incident coordinates
        $coords = explode(', ', $report->map_coordinates);
        $incidentLat = (float)$coords[0];
        $incidentLng = (float)$coords[1];

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $request->responder_latitude,
            $request->responder_longitude,
            $incidentLat,
            $incidentLng
        );

        // Calculate ETA (distance ÷ 40 km/h)
        $travelTimeHours = $distance / 40;
        $travelTimeMinutes = $travelTimeHours * 60;
        $estimatedArrival = now()->addMinutes($travelTimeMinutes);

        // Update report
        $report->update([
            'status' => 'assigned',
            'responder_name' => $request->user()->name,
            'responder_contact_no' => $request->user()->mobile_no ?? 'N/A',
            'plate_no' => $request->plate_no,
            'distance' => round($distance, 2),
            'estimated_arrival' => $estimatedArrival,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report accepted successfully',
            'report' => $report->load(['emergency', 'incident', 'barangay', 'user'])
        ]);
    }

    // Calculate distance between two coordinates (Haversine formula)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // Update responder location (store in cache)
    public function updateResponderLocation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Store in cache for 5 minutes
        Cache::put("responder_location_{$id}", [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_at' => now()->toDateTimeString()
        ], 300);

        return response()->json([
            'success' => true,
            'message' => 'Location updated'
        ]);
    }

    // Get responder location (from cache)
    public function getResponderLocation(Request $request, $id)
    {
        $location = Cache::get("responder_location_{$id}");

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not available'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'location' => $location
        ]);
    }

    // Get reports where current user is the responder
    public function getResponderCases(Request $request)
    {
        $reports = IncidentReport::where('responder_name', $request->user()->full_name)
            ->whereIn('status', ['assigned', 'arriving'])
            ->with(['emergency', 'incident', 'barangay', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }
}