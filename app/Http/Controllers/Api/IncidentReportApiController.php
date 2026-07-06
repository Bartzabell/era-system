<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\Emergency;
use App\Models\Incident;
use App\Models\Barangay;
use App\Models\SiteLocation;
use App\Services\ClosestFacilityFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\PriorityScoreCalculator;

class IncidentReportApiController extends Controller
{
    // Common relations to load with reports
    private $reportRelations = ['emergency', 'incident', 'barangay', 'user'];

    // Get dropdowns data
    public function getDropdowns()
    {
        return response()->json([
            'success' => true,
            'emergencies' => Emergency::all(),
            'incidents' => Incident::all(),
            'barangays' => Barangay::all()
        ]);
    }

    // Create incident report
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emergency_id' => 'nullable|exists:emergencies,id',
            'incident_id' => 'nullable|exists:incidents,id',
            'other_incident' => 'nullable|string|max:255',  // ✅ Add validation
            'barangay_id' => 'nullable|exists:barangays,id',
            'casualty_count' => 'required|integer|min:0',
            'map_coordinates' => 'nullable|string',
            'attachment' => 'nullable|string|url',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        // Parse coordinates
        $coords = explode(', ', $request->map_coordinates);
        $incidentLat = (float)$coords[0];
        $incidentLng = (float)$coords[1];

        // ✅ Determine if using incident or other_incident
        $incident = null;
        $distance = null;
        $priorityData = null;

        if ($request->incident_id) {
            // Has specific incident - use full calculation
            $incident = Incident::findOrFail($request->incident_id);

            $facilityFinder = new ClosestFacilityFinder();
            $facilityResult = $facilityFinder->findClosest(
                $incident->incident_name,
                $incidentLat,
                $incidentLng
            );

            $distance = $facilityResult['success'] ? $facilityResult['distance_km'] : null;

            $calculator = new PriorityScoreCalculator();
            $priorityData = $calculator->calculate($incident, $distance);
        } elseif ($request->other_incident) {
            // Has other incident - use default P3/Moderate
            $priorityData = [
                'priority_score' => 5,
                'priority_level' => 'P3',
                'priority_label' => 'Moderate'
            ];
            $distance = null;
        } else {
            // Fallback (shouldn't happen if validation works)
            $calculator = new PriorityScoreCalculator();
            $priorityData = $calculator->calculate(null, null);
            $distance = null;
        }

        $report = IncidentReport::create([
            'user_id' => $request->user()->id,
            'emergency_id' => $request->emergency_id,
            'incident_id' => $request->incident_id,
            'other_incident' => $request->other_incident,  // ✅ Save this
            'barangay_id' => $request->barangay_id,
            'casualty_count' => $request->casualty_count,
            'map_coordinates' => $request->map_coordinates,
            'attachment' => $request->attachment,
            'severity_level' => 'low',
            'status' => 'verifying',
            'distance' => $distance,
            'remarks' => $request->remarks,
            'priority_score' => $priorityData['priority_score'],
            'priority_level' => $priorityData['priority_level'],
            'priority_label' => $priorityData['priority_label'],
            'reported_at' => now(),
        ]);

        $report->update([
            'incident_code' => 'IND-' . now()->year . '-' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
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

    public function getUserReports(Request $request)
    {
        $reports = IncidentReport::where('user_id', $request->user()->id)
            ->with($this->reportRelations)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(null, ['reports' => $reports]);
    }

    // Update responder's current facility location
    public function updateResponderFacility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_location_id' => 'required|exists:site_locations,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $user = $request->user();

            // Only responders can update their facility
            if ($user->role !== 'responder') {
                return $this->errorResponse('Only responders can update facility location', 403);
            }

            $user->update([
                'site_location_id' => $request->site_location_id
            ]);

            return $this->successResponse(
                'Facility location updated successfully',
                ['user' => $user]
            );
        } catch (\Exception $e) {
            Log::error('Update Responder Facility Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update facility location', 500);
        }
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

        // Check if responder already has ongoing case
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

        // Get responder's current site location
        $responder = $request->user();
        $currentSite = $responder->siteLocation;

        if (!$currentSite) {
            return $this->errorResponse('Please set your current facility location first.', 400);
        }

        // Parse site coordinates
        $siteCoords = explode(', ', $currentSite->coordinates);
        $siteLat = (float)$siteCoords[0];
        $siteLng = (float)$siteCoords[1];

        // Get distance from site to incident
        $distanceData = $this->getDistanceFromGoogle(
            $siteLat,
            $siteLng,
            $incidentLat,
            $incidentLng
        );

        if (!$distanceData) {
            return $this->errorResponse('Failed to calculate distance. Please try again.', 500);
        }

        // Calculate ETA
        $travelTimeMinutes = $distanceData['duration_minutes'] ?? ($distanceData['distance_km'] / 30 * 60);
        $estimatedArrival = now()->addMinutes($travelTimeMinutes);

        // Recalculate priority score with actual distance
        $incident = $report->incident;
        $calculator = new PriorityScoreCalculator();
        $priorityData = $calculator->calculate($incident, $distanceData['distance_km']);

        // Update report
        $report->update([
            'status' => 'assigned',
            'responder_name' => $request->user()->full_name,
            'responder_contact_no' => $request->user()->mobile_no ?? '',
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
            'responder_count' => 'nullable|integer|min:0',
            'minor_casualty_count' => 'nullable|integer|min:0',
            'serious_casualty_count' => 'nullable|integer|min:0',
            'deceased_casualty_count' => 'nullable|integer|min:0',
            'severity_level' => 'nullable|in:low,mid,high',
            'datetime_arrived' => 'nullable|date',
            'remarks' => 'nullable|string',
            'responder_remarks' => 'nullable|string', // ✅ NEW
            'treatment_provided' => 'nullable|string', // ✅ NEW
            'responder_attachment' => 'nullable|string|url', // ✅ NEW
            'site_location_id' => 'nullable|exists:site_locations,id',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $report = IncidentReport::findOrFail($id);

        // Authorization check
        $user = $request->user();

        // Citizen can only update their own reports
        if ($user->role === 'citizen' && $report->user_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Responder can only update reports they accepted
        if ($user->role === 'responder' && $report->responder_name !== $user->full_name) {
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

    // Get all site locations
    public function getSiteLocations()
    {
        try {
            $siteLocations = SiteLocation::select('id', 'site_name', 'site_type', 'site_category', 'coordinates')
                ->get();

            return $this->successResponse(null, ['site_locations' => $siteLocations]);
        } catch (\Exception $e) {
            Log::error('Get Site Locations Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch site locations', 500);
        }
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

    // Get attachments from folder
    public function getAttachments($id)
    {
        try {
            $report = IncidentReport::findOrFail($id);

            if (!$report->attachment) {
                return $this->successResponse(null, ['attachments' => []]);
            }

            // Handle old Google Drive URLs
            if (str_starts_with($report->attachment, 'http')) {
                return $this->successResponse(null, [
                    'attachments' => [['url' => $report->attachment, 'type' => 'legacy']]
                ]);
            }

            // New folder-based attachments
            $files = Storage::disk('public')->files($report->attachment);
            $attachments = array_map(function ($file) {
                return [
                    'url' => asset('storage/' . $file),
                    'name' => basename($file),
                    'type' => 'file'
                ];
            }, $files);

            return $this->successResponse(null, ['attachments' => $attachments]);
        } catch (\Exception $e) {
            Log::error('Get Attachments Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch attachments', 500);
        }
    }

    // Upload multiple attachments
    public function uploadAttachments(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'attachments' => 'required|array|max:5',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,mp4,mov|max:51200' // 50MB per file
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $report = IncidentReport::findOrFail($id);
            $folderPath = 'incident-attachments/' . $report->incident_code;

            foreach ($request->file('attachments') as $file) {
                $file->store($folderPath, 'public');
            }

            // Update attachment field with folder path
            $report->update(['attachment' => $folderPath]);

            return $this->successResponse('Attachments uploaded successfully', [
                'folder' => $folderPath
            ]);
        } catch (\Exception $e) {
            Log::error('Upload Attachments Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to upload attachments', 500);
        }
    }

    /**
     * Upload responder attachments to folder: incident-attachments/{incident_code}/Responder Attachments/
     */
    public function uploadResponderAttachments(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'attachments' => 'required|array|max:5',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,mp4,mov|max:51200' // 50MB per file
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            $report = IncidentReport::findOrFail($id);
            
            // Authorization check
            $user = $request->user();
            if ($user->role === 'responder' && $report->responder_name !== $user->full_name) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $folderPath = 'incident-attachments/' . $report->incident_code . '/Responder Attachments';

            foreach ($request->file('attachments') as $file) {
                $file->store($folderPath, 'public');
            }

            $report->update([
                'responder_attachment' => $folderPath
            ]);

            return $this->successResponse('Responder attachments uploaded successfully', [
                'folder' => $folderPath
            ]);
        } catch (\Exception $e) {
            Log::error('Upload Responder Attachments Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to upload attachments', 500);
        }
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
        if ($request->has('responder_count')) $updateData['responder_count'] = $request->responder_count;
        if ($request->has('minor_casualty_count')) $updateData['minor_casualty_count'] = $request->minor_casualty_count;
        if ($request->has('serious_casualty_count')) $updateData['serious_casualty_count'] = $request->serious_casualty_count;
        if ($request->has('deceased_casualty_count')) $updateData['deceased_casualty_count'] = $request->deceased_casualty_count;
        $minor = $request->minor_casualty_count ?? $report->minor_casualty_count ?? 0;
        $serious = $request->serious_casualty_count ?? $report->serious_casualty_count ?? 0;
        $deceased = $request->deceased_casualty_count ?? $report->deceased_casualty_count ?? 0;
        $updateData['casualty_count'] = $minor + $serious + $deceased;
        if ($request->has('severity_level')) $updateData['severity_level'] = $request->severity_level;
        if ($request->has('datetime_arrived')) $updateData['datetime_arrived'] = $request->datetime_arrived;
        if ($request->has('remarks')) $updateData['remarks'] = $request->remarks;
        if ($request->has('responder_remarks')) $updateData['responder_remarks'] = $request->responder_remarks;
        if ($request->has('treatment_provided')) $updateData['treatment_provided'] = $request->treatment_provided;
        if ($request->has('responder_attachment')) $updateData['responder_attachment'] = $request->responder_attachment;
        if ($request->has('site_location_id')) $updateData['site_location_id'] = $request->site_location_id;
        if ($request->has('distance_km')) $updateData['distance_km'] = $request->distance_km;

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
