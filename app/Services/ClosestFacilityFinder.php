<?php

namespace App\Services;

use App\Models\SiteLocation;
use Illuminate\Support\Facades\Log;

class ClosestFacilityFinder
{
    /**
     * Mapping of incident keywords to matching site types
     */
    private $incidentToSiteTypes = [
        // Medical
        "Difficulty Breathing" => ["Hospital", "Health Office", "Lying-in Clinic", "Private Clinic"],
        "Cardiac" => ["Hospital", "Health Office", "Private Clinic"],
        "Stroke" => ["Hospital", "Health Office", "Private Clinic"],
        "Seizure" => ["Hospital", "Health Office", "Private Clinic"],
        "Unconscious" => ["Hospital", "Health Office", "Private Clinic"],
        "Fever" => ["Hospital", "Health Office", "Lying-in Clinic", "Private Clinic"],
        "Diabetic" => ["Hospital", "Health Office", "Private Clinic"],
        
        // Trauma
        "Motorcycle Accident" => ["Hospital", "Health Office"],
        "Car-to-Car Collision" => ["Hospital", "Health Office"],
        "Pedestrian Hit" => ["Hospital", "Health Office"],
        "Truck / Bus Accident" => ["Hospital", "Health Office"],
        "Hit and Run" => ["Hospital", "Health Office"],
        "Road Rollover" => ["Hospital", "Health Office"],
        "Severe Bleeding" => ["Hospital", "Health Office"],
        "Head Injury" => ["Hospital", "Health Office"],
        "Fracture" => ["Hospital", "Health Office"],
        "Stab Wound" => ["Hospital", "Health Office"],
        "Gunshot Wound" => ["Hospital", "Health Office"],
        "Fall from Height" => ["Hospital", "Health Office"],
        
        // Fire
        "Residential Fire" => ["Fire Station"],
        "Electrical Fire" => ["Fire Station"],
        "Grass Fire" => ["Fire Station"],
        "Vehicle Fire" => ["Fire Station"],
        "LPG / Gas Leak" => ["Fire Station"],
        
        // Other
        "Drowning Incident" => ["Hospital", "Fire Station", "Police Station"],
        "Flood Rescue" => ["Fire Station", "Police Station"],
        "Entrapment" => ["Fire Station", "Police Station"],
        "Collapsed Structure" => ["Fire Station", "Police Station"],
        "Missing Person Search" => ["Police Station", "Fire Station"],
        "Physical Assault" => ["Hospital", "Police Station"],
        "Robbery" => ["Police Station"],
        "Domestic Violence" => ["Police Station"],
        "Shooting Incident" => ["Police Station", "Hospital"],
        "Stabbing Incident" => ["Police Station", "Hospital"],
        "Flood" => ["Fire Station"],
        "Typhoon Response" => ["Fire Station", "Police Station"],
        "Landslide" => ["Fire Station"],
        "Earthquake Response" => ["Fire Station"],
        "Evacuation Assistance" => ["Police Station", "Fire Station"],
    ];

    /**
     * Find closest facility for incident
     * @param string $incidentName
     * @param float $incidentLat
     * @param float $incidentLng
     * @returns array { success, site_location, distance_km, error }
     */
    public function findClosest($incidentName, $incidentLat, $incidentLng)
    {
        try {
            Log::info('[ClosestFacilityFinder] Finding closest facility for: ' . $incidentName);

            // Get matching site types
            $matchingSiteTypes = $this->getMatchingSiteTypes($incidentName);

            if (empty($matchingSiteTypes)) {
                Log::warning('[ClosestFacilityFinder] No matching site types for: ' . $incidentName);
                return [
                    'success' => false,
                    'site_location' => null,
                    'distance_km' => null,
                    'error' => 'No matching facilities for this incident type'
                ];
            }

            // Get all matching sites
            $matchingSites = SiteLocation::whereIn('site_type', $matchingSiteTypes)
                ->get();

            if ($matchingSites->isEmpty()) {
                Log::warning('[ClosestFacilityFinder] No facilities found of matching types');
                return [
                    'success' => false,
                    'site_location' => null,
                    'distance_km' => null,
                    'error' => 'No facilities available'
                ];
            }

            Log::info('[ClosestFacilityFinder] Found ' . $matchingSites->count() . ' matching facilities');

            // Calculate distance to each site and find closest
            $closestSite = null;
            $closestDistance = PHP_INT_MAX;

            foreach ($matchingSites as $site) {
                $coords = explode(', ', $site->coordinates);
                $siteLat = (float)$coords[0];
                $siteLng = (float)$coords[1];

                // Calculate distance using Google Maps API
                $distanceData = $this->getDistanceFromGoogle(
                    $incidentLat,
                    $incidentLng,
                    $siteLat,
                    $siteLng
                );

                if ($distanceData && $distanceData['distance_km'] < $closestDistance) {
                    $closestDistance = $distanceData['distance_km'];
                    $closestSite = $site;
                }
            }

            if (!$closestSite) {
                Log::error('[ClosestFacilityFinder] Could not calculate any distances');
                return [
                    'success' => false,
                    'site_location' => null,
                    'distance_km' => null,
                    'error' => 'Could not calculate distances'
                ];
            }

            Log::info('[ClosestFacilityFinder] Closest facility: ' . $closestSite->site_name . ', Distance: ' . $closestDistance . ' km');

            return [
                'success' => true,
                'site_location' => $closestSite,
                'distance_km' => $closestDistance
            ];

        } catch (\Exception $e) {
            Log::error('[ClosestFacilityFinder] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'site_location' => null,
                'distance_km' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get matching site types for incident
     */
    private function getMatchingSiteTypes($incidentName)
    {
        foreach ($this->incidentToSiteTypes as $keyword => $types) {
            if (stripos($incidentName, $keyword) !== false) {
                return $types;
            }
        }
        return [];
    }

    /**
     * Calculate distance using Google Maps Distance Matrix API
     */
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
            Log::error('[ClosestFacilityFinder] Distance API Error: ' . $e->getMessage());
            return [
                'distance_km' => $this->calculateDistance($originLat, $originLng, $destLat, $destLng),
                'duration_minutes' => null
            ];
        }
    }

    /**
     * Fallback: Calculate distance using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }
}