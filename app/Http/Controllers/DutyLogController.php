<?php

namespace App\Http\Controllers;

use App\Models\DutyLog;
use App\Models\Responder;
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

        $sessions = DutyLog::with('user:id,full_name')
            ->where('checked_in_at', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('checked_out_at')
                ->orWhere('checked_out_at', '>=', $startDate);
            })
            ->get();

        $activeResponders = Responder::with('user:id,full_name')
            ->where('is_active', true)
            ->get();

        $dutyLogs = [];
        $offDutyLogs = [];

        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            $dateKey  = $day->format('Y-m-d');
            $dayStart = $day->copy()->startOfDay();
            $dayEnd   = $day->copy()->endOfDay();

            $onDuty = $sessions->filter(fn ($s) =>
                $s->checked_in_at <= $dayEnd
                && (is_null($s->checked_out_at) || $s->checked_out_at >= $dayStart)
            );

            $onDutyUserIds = $onDuty->pluck('user_id')->unique()->all();

            if ($onDuty->isNotEmpty()) {
                $dutyLogs[$dateKey] = $onDuty->map(fn ($s) => [
                    'name' => $s->user?->full_name ?? 'Unknown',
                    'time' => $s->checked_in_at->format('M d, Y | h:i A'),
                ])->values();
            }

            $offDuty = $activeResponders
                ->reject(fn ($r) => in_array($r->user_id, $onDutyUserIds))
                ->map(fn ($r) => ['name' => $r->user?->full_name ?? 'Unknown'])
                ->values();

            if ($offDuty->isNotEmpty()) {
                $offDutyLogs[$dateKey] = $offDuty;
            }
        }

        return Inertia::render('DutyLog/Index', [
            'dutyLogs'    => $dutyLogs,
            'offDutyLogs' => $offDutyLogs,
            'month'       => $month,
            'year'        => $year,
        ]);
    }
}