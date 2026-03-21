<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;
use App\Models\Responder;
use App\Exports\MonthlyReportExport;
use App\Exports\CitizenReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeIncidents = IncidentReport::whereNotIn('status', ['resolved', 'cancelled'])
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

        $resolvedToday = IncidentReport::where('status', 'resolved')
            ->whereDate('updated_at', Carbon::today())
            ->whereNull('deleted_at')
            ->count();

        $incidentMarkers = IncidentReport::with(['barangay', 'incident', 'emergency'])
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->whereNull('deleted_at')
            ->whereNotNull('map_coordinates')
            ->get()
            ->map(function ($report) {
                $coords = $this->parseCoordinates($report->map_coordinates);

                return [
                    'id'             => $report->id,
                    'lat'            => $coords['lat'],
                    'lng'            => $coords['lng'],
                    'priority_level' => $report->priority_level,
                    'priority_label' => $report->priority_label,
                    'priority_score' => $report->priority_score,
                    'status'         => $report->status,
                    'incident_type'  => $report->incident->incident_name ?? 'Unknown',
                    'emergency_type' => $report->emergency->emergency_name ?? 'Unknown',
                    'barangay'       => $report->barangay->barangay_name ?? 'Unknown',
                    'casualty_count' => $report->casualty_count,
                    'created_at'     => $report->created_at->diffForHumans(),
                ];
            });

        // Sort feed by priority_score desc (most urgent first), then by created_at desc
        $incidentFeed = IncidentReport::with(['incident', 'barangay'])
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->whereNull('deleted_at')
            ->orderByRaw('COALESCE(priority_score, 0) DESC')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($report) {
                $coords = $this->parseCoordinates($report->map_coordinates);

                return [
                    'id'             => $report->id,
                    'incident_name'  => $report->incident->incident_name ?? 'Unknown Incident',
                    'landmark'       => $report->barangay->barangay_name ?? 'Unknown Location',
                    'coordinates'    => $coords,
                    'time_ago'       => $this->formatTimeAgo($report->created_at),
                    'priority_score' => $report->priority_score,
                    'priority_level' => $report->priority_level,
                    'priority_label' => $report->priority_label,
                    'status'         => $report->status,
                    'created_at'     => $report->created_at,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeIncidents'  => $activeIncidents,
                'activeResponders' => $activeResponders,
                'avgResponseTime'  => $avgResponseTimeFormatted,
                'resolvedToday'    => $resolvedToday,
            ],
            'incidentMarkers' => $incidentMarkers,
            'incidentFeed'    => $incidentFeed,
        ]);
    }

    public function exportMonthlyReport(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year  = $request->input('year', Carbon::now()->year);

        $fileName = 'monthly_incident_report_' . Carbon::createFromDate($year, $month, 1)->format('F_Y') . '.xlsx';

        return Excel::download(new MonthlyReportExport($month, $year), $fileName);
    }

    public function exportCitizenReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth());

        $fileName = 'citizen_incident_reports_' . Carbon::now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CitizenReportExport($startDate, $endDate), $fileName);
    }

    private function parseCoordinates($coordinates)
    {
        if (is_string($coordinates)) {
            $decoded = json_decode($coordinates, true);
            if ($decoded && isset($decoded['lat']) && isset($decoded['lng'])) {
                return $decoded;
            }

            $parts = explode(',', $coordinates);
            if (count($parts) === 2) {
                return [
                    'lat' => floatval(trim($parts[0])),
                    'lng' => floatval(trim($parts[1])),
                ];
            }
        }

        return ['lat' => 14.5995, 'lng' => 120.9842];
    }

    private function formatTimeAgo($createdAt)
    {
        $now            = Carbon::now();
        $created        = Carbon::parse($createdAt);
        $diffInMinutes  = $created->diffInMinutes($now);

        if ($diffInMinutes < 1) {
            return 'Just now';
        } elseif ($diffInMinutes < 60) {
            return $diffInMinutes . ' min ago';
        } elseif ($diffInMinutes < 1440) {
            $hours = floor($diffInMinutes / 60);
            return $hours . ' hr ago';
        } else {
            $days = floor($diffInMinutes / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }
}
