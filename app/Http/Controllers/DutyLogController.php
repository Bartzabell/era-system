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

        // Every session that could be "the latest known session" for any day this month
        $sessionsByUser = DutyLog::with('user:id,full_name')
            ->where('checked_in_at', '<=', $endDate)
            ->orderBy('checked_in_at')
            ->get()
            ->groupBy('user_id');

        $activeResponders = Responder::with('user:id,full_name')
            ->where('is_active', true)
            ->get();

        $dutyLogs = [];
        $offDutyLogs = [];

        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            $dateKey = $day->format('Y-m-d');
            $dayEnd  = $day->copy()->endOfDay();

            $onDutyThisDay = collect();

            foreach ($activeResponders as $responder) {
                $userSessions = $sessionsByUser->get($responder->user_id, collect());

                // The most recent session that had already started by the end of this day
                $latest = $userSessions->filter(fn ($s) => $s->checked_in_at <= $dayEnd)->last();

                if (!$latest) {
                    continue; // hadn't checked in yet as of this day
                }

                // Still open as of this day if never checked out, or checked out AFTER this day ended
                $isOpen = is_null($latest->checked_out_at) || $latest->checked_out_at->gt($dayEnd);

                if ($isOpen) {
                    $onDutyThisDay->push($latest);
                }
            }

            if ($onDutyThisDay->isNotEmpty()) {
                $dutyLogs[$dateKey] = $onDutyThisDay->map(fn ($s) => [
                    'name' => $s->user?->full_name ?? 'Unknown',
                    'time' => $s->checked_in_at->format('M d, Y | h:i A'),
                ])->values();
            }

            $onDutyUserIds = $onDutyThisDay->pluck('user_id')->all();

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