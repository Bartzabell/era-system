<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;
use App\Models\Responder;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeIncidents = IncidentReport::whereNotIn('status', ['resolved', 'completed'])
            ->whereNull('deleted_at')
            ->count();

        $activeResponders = Responder::where('is_active', true)
            ->whereNull('deleted_at')
            ->count();

        $avgResponseTime = IncidentReport::whereNotNull('datetime_arrived')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($report) {
                $created = Carbon::parse($report->created_at);
                $arrived = Carbon::parse($report->datetime_arrived);
                return $created->diffInMinutes($arrived);
            })
            ->avg();

        $avgResponseTimeFormatted = $avgResponseTime
            ? round($avgResponseTime) . ' min'
            : '0 min';

        $resolvedToday = IncidentReport::whereIn('status', ['resolved', 'completed'])
            ->whereDate('updated_at', Carbon::today())
            ->whereNull('deleted_at')
            ->count();

        // Get active incidents with coordinates for the map
        $incidentMarkers = IncidentReport::with(['barangay', 'incident', 'emergency'])
            ->whereNotIn('status', ['resolved', 'completed'])
            ->whereNull('deleted_at')
            ->whereNotNull('map_coordinates')
            ->get()
            ->map(function ($report) {
                // Parse coordinates (assuming format: "lat,lng" or JSON)
                $coords = $this->parseCoordinates($report->map_coordinates);

                return [
                    'id' => $report->id,
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'severity' => $report->severity_level,
                    'status' => $report->status,
                    'incident_type' => $report->incident->name ?? 'Unknown',
                    'emergency_type' => $report->emergency->name ?? 'Unknown',
                    'barangay' => $report->barangay->name ?? 'Unknown',
                    'casualty_count' => $report->casualty_count,
                    'created_at' => $report->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeIncidents' => $activeIncidents,
                'activeResponders' => $activeResponders,
                'avgResponseTime' => $avgResponseTimeFormatted,
                'resolvedToday' => $resolvedToday,
            ],
            'incidentMarkers' => $incidentMarkers,
        ]);
    }

    private function parseCoordinates($coordinates)
    {
        // Handle different coordinate formats
        if (is_string($coordinates)) {
            // Try JSON first
            $decoded = json_decode($coordinates, true);
            if ($decoded && isset($decoded['lat']) && isset($decoded['lng'])) {
                return $decoded;
            }

            // Try comma-separated format
            $parts = explode(',', $coordinates);
            if (count($parts) === 2) {
                return [
                    'lat' => floatval(trim($parts[0])),
                    'lng' => floatval(trim($parts[1]))
                ];
            }
        }

        // Default fallback (Philippines center)
        return ['lat' => 14.5995, 'lng' => 120.9842];
    }
}
