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
use Illuminate\Support\Facades\Log;

class IncidentReportApiController extends Controller
{
    // Common relations to load with reports
    private $reportRelations = ['emergency', 'incident', 'barangay', 'user'];

    // Get dropdowns data
    public function getDropdowns()
    {
        return response()->json([
            'success' => true,
            'emergencies' => Emergency::select('id', 'emergency_name', 'severity_level')->get(),
            'incidents' => Incident::select('id', 'incident_name', 'severity_level')->get(),
            'barangays' => Barangay::select('id', 'barangay_name', 'landmark')->get()
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
            return $this->errorResponse($validator->errors(), 422);
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

        return $this->successResponse('Incident report submitted successfully', ['report' => $report], 201);
    }

    // Get user's incident reports
    public function index(Request $request)
    {
        $reports = IncidentReport::where('user_id', $request->user()->id)
            ->with($this->reportRelations)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(null, ['reports' => $reports]);
    }

    // Get all pending reports (waiting status)
    public function getPendingReports(Request $request)
    {
        $reports = IncidentReport::where('status', 'waiting')
            ->with($this->reportRelations)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(null, ['reports' => $reports]);
    }

    // Get responder's cases
    public function getResponderCases(Request $request)
    {
        $reports = IncidentReport::where('responder_name', $request->user()->full_name)
            ->whereIn('status', ['assigned', 'arriving', 'resolved', 'cancelled'])
            ->with($this->reportRelations)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(null, ['reports' => $reports]);
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
            return $this->errorResponse($validator->errors(), 422);
        }

        // Check if responder already has an ongoing case
        $ongoingCase = IncidentReport::where('responder_name', $request->user()->full_name)
            ->whereIn('status', ['assigned', 'arriving'])
            ->first();

        if ($ongoingCase) {
            return $this->errorResponse('You already have an ongoing case. Please resolve it first.', 400);
        }

        $report = IncidentReport::findOrFail($id);

        if ($report->status !== 'waiting') {
            return $this->errorResponse('This report has already been accepted.', 400);
        }

        // Parse incident coordinates
        $coords = explode(', ', $report->map_coordinates);
        $incidentLat = (float)$coords[0];
        $incidentLng = (float)$coords[1];

        // Get distance and duration
        $distanceData = $this->getDistanceFromGoogle(
            $request->responder_latitude,
            $request->responder_longitude,
            $incidentLat,
            $incidentLng
        );

        if (!$distanceData) {
            return $this->errorResponse('Failed to calculate distance. Please try again.', 500);
        }

        // Calculate ETA
        $travelTimeMinutes = $distanceData['duration_minutes'] ?? ($distanceData['distance_km'] / 30 * 60);
        $estimatedArrival = now()->addMinutes($travelTimeMinutes);

        // Update report
        $report->update([
            'status' => 'assigned',
            'responder_name' => $request->user()->full_name,
            'responder_contact_no' => $request->user()->mobile_no ?? 'N/A',
            'plate_no' => $request->plate_no,
            'distance' => $distanceData['distance_km'],
            'estimated_arrival' => $estimatedArrival,
        ]);

        return $this->successResponse(
            'Report accepted successfully',
            ['report' => $report->load($this->reportRelations)]
        );
    }

    // Update report - handles ALL field updates
    public function updateReport(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:arriving,resolved',
            'barangay_id' => 'nullable|exists:barangays,id',
            'severity_level' => 'nullable|in:low,mid,high',
            'datetime_arrived' => 'nullable|date',
            'remarks' => 'nullable|string',
            'responder_remarks' => 'nullable|string', // ✅ NEW
            'treatment_provided' => 'nullable|string', // ✅ NEW
            'responder_attachment' => 'nullable|string|url', // ✅ NEW
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $report = IncidentReport::findOrFail($id);

        // Authorization check
        $user = $request->user();
        
        // Citizen can only update their own reports
        if ($user->role === 'Citizen' && $report->user_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Responder can only update reports they accepted
        if ($user->role === 'Responder' && $report->responder_name !== $user->full_name) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Status validation
        if ($request->has('status')) {
            $statusCheck = $this->validateStatusTransition($report->status, $request->status);
            if ($statusCheck) return $statusCheck;
        }

        // Build update data
        $updateData = $this->buildUpdateData($request, $report);
        $updateData['updated_by'] = $user->id;

        $report->update($updateData);

        // Clear cache if resolved
        if ($request->status === 'resolved') {
            Cache::forget("responder_location_{$id}");
        }

        return $this->successResponse(
            'Report updated successfully',
            ['report' => $report->load($this->reportRelations)]
        );
    }

    // Cancel report
    public function cancelReport(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cancel_remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $report = IncidentReport::findOrFail($id);
        $user = $request->user();

        // Citizen can only cancel their own waiting reports
        if ($user->role === 'Citizen') {
            if ($report->user_id !== $user->id) {
                return $this->errorResponse('Unauthorized', 403);
            }

            if ($report->status !== 'waiting') {
                return $this->errorResponse('Can only cancel waiting reports', 400);
            }
        }

        // Responder can cancel anytime (except resolved)
        if ($user->role === 'Responder') {
            if ($report->responder_name !== $user->full_name && $report->status !== 'waiting') {
                return $this->errorResponse('Unauthorized', 403);
            }

            if ($report->status === 'resolved') {
                return $this->errorResponse('Cannot cancel resolved reports', 400);
            }
        }

        $report->update([
            'status' => 'cancelled',
            'cancel_remarks' => $request->cancel_remarks,
            'cancelled_by' => $user->role, // Store role: "Citizen" or "Responder"
            'updated_by' => $user->id,
        ]);

        // Clear cache
        Cache::forget("responder_location_{$id}");

        return $this->successResponse(
            'Report cancelled successfully',
            ['report' => $report->load($this->reportRelations)]
        );
    }

    // Update responder location (cache)
    public function updateResponderLocation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        Cache::put("responder_location_{$id}", [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_at' => now()->toDateTimeString()
        ], 300);

        return $this->successResponse('Location updated');
    }

    // Get responder location (cache)
    public function getResponderLocation(Request $request, $id)
    {
        $location = Cache::get("responder_location_{$id}");

        if (!$location) {
            return $this->errorResponse('Location not available', 404);
        }

        return $this->successResponse(null, ['location' => $location]);
    }

    // ========== HELPER METHODS ==========

    // Standard success response
    private function successResponse($message = null, $data = [], $code = 200)
    {
        $response = ['success' => true];
        if ($message) $response['message'] = $message;
        return response()->json(array_merge($response, $data), $code);
    }

    // Standard error response
    private function errorResponse($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            is_array($message) ? 'errors' : 'message' => $message
        ], $code);
    }

    // Check report authorization
    private function checkReportAuthorization($user, $report)
    {
        if ($user->role === 'Citizen' && $report->user_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($user->role === 'Responder' && $report->responder_name !== $user->full_name) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return null;
    }

    // Validate status transition
    private function validateStatusTransition($currentStatus, $newStatus)
    {
        if ($newStatus === 'arriving' && $currentStatus !== 'assigned') {
            return $this->errorResponse('Can only mark as arriving from assigned status', 400);
        }

        if ($newStatus === 'resolved' && !in_array($currentStatus, ['assigned', 'arriving'])) {
            return $this->errorResponse('Can only mark as resolved from assigned or arriving status', 400);
        }

        return null;
    }

    // Build update data array
    private function buildUpdateData($request, $report)
    {
        $updateData = [];
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        
        if ($request->has('barangay_id')) $updateData['barangay_id'] = $request->barangay_id;
        if ($request->has('severity_level')) $updateData['severity_level'] = $request->severity_level;
        if ($request->has('datetime_arrived')) $updateData['datetime_arrived'] = $request->datetime_arrived;
        if ($request->has('remarks')) $updateData['remarks'] = $request->remarks;
        if ($request->has('responder_remarks')) $updateData['responder_remarks'] = $request->responder_remarks;
        if ($request->has('treatment_provided')) $updateData['treatment_provided'] = $request->treatment_provided;
        if ($request->has('responder_attachment')) $updateData['responder_attachment'] = $request->responder_attachment;
        
        return $updateData;
    }

    // Check cancel permissions
    private function checkCancelPermissions($user, $report)
    {
        if ($user->role === 'Citizen') {
            if ($report->user_id !== $user->id) {
                return $this->errorResponse('Unauthorized', 403);
            }
            if ($report->status !== 'waiting') {
                return $this->errorResponse('Can only cancel waiting reports', 400);
            }
        }

        if ($user->role === 'Responder') {
            if ($report->responder_name !== $user->full_name && $report->status !== 'waiting') {
                return $this->errorResponse('Unauthorized', 403);
            }
            if ($report->status === 'resolved') {
                return $this->errorResponse('Cannot cancel resolved reports', 400);
            }
        }

        return null;
    }

    // Calculate distance (Haversine formula)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    // Get distance from Google API
    private function getDistanceFromGoogle($originLat, $originLng, $destLat, $destLng)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        
        if (!$apiKey) {
            return [
                'distance_km' => $this->calculateDistance($originLat, $originLng, $destLat, $destLng),
                'duration_minutes' => null
            ];
        }

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?" . http_build_query([
            'origins' => "{$originLat},{$originLng}",
            'destinations' => "{$destLat},{$destLng}",
            'mode' => 'driving',
            'key' => $apiKey
        ]);

        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if ($data['status'] === 'OK' && isset($data['rows'][0]['elements'][0])) {
                $element = $data['rows'][0]['elements'][0];
                
                if ($element['status'] === 'OK') {
                    return [
                        'distance_km' => round($element['distance']['value'] / 1000, 2),
                        'duration_minutes' => round($element['duration']['value'] / 60)
                    ];
                }
            }

            return [
                'distance_km' => $this->calculateDistance($originLat, $originLng, $destLat, $destLng),
                'duration_minutes' => null
            ];

        } catch (\Exception $e) {
            Log::error('Distance Matrix API Error: ' . $e->getMessage());
            return [
                'distance_km' => $this->calculateDistance($originLat, $originLng, $destLat, $destLng),
                'duration_minutes' => null
            ];
        }
    }
}