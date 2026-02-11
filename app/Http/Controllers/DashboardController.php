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

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeIncidents' => $activeIncidents,
                'activeResponders' => $activeResponders,
                'avgResponseTime' => $avgResponseTimeFormatted,
                'resolvedToday' => $resolvedToday,
            ]
        ]);
    }
}
