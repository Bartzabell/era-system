<?php

namespace App\Http\Controllers;

use App\Models\DutyLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DutyLogController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $logs = DutyLog::with('user:id,full_name')
            ->whereBetween('duty_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('checked_in_at')
            ->get();

        $groupedLogs = $logs->groupBy(fn ($log) => $log->duty_date->format('Y-m-d'))
            ->map(function ($group) {
                return $group->map(fn ($log) => [
                    'name' => $log->user?->full_name ?? 'Unknown',
                    'time' => $log->checked_in_at->format('h:i A'),
                ]);
            });

        return Inertia::render('DutyLog/Index', [
            'dutyLogs' => $groupedLogs,
            'month'    => $month,
            'year'     => $year,
        ]);
    }
}