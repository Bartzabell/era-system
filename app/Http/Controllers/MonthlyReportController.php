<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;
use App\Models\Barangay;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyBarangayReportExport;

class MonthlyReportController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = IncidentReport::with([
            'barangay:id,barangay_name,landmark',
            'incident:id,incident_name',
        ]);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    private function aggregateReports(Request $request): \Illuminate\Support\Collection
    {
        $reports = $this->buildQuery($request)->get();

        $grouped = $reports->groupBy('barangay_id');

        $rows = collect();
        $index = 1;

        foreach ($grouped as $barangayId => $items) {
            $first    = $items->first();
            $barangay = $first->barangay;

            $minor   = $items->sum('minor_casualty_count');
            $serious = $items->sum('serious_casualty_count');
            $dead    = $items->sum('deceased_casualty_count');

            $topIncidents = $items
                ->groupBy(fn($r) => $r->incident?->incident_name ?? 'Unknown')
                ->sortByDesc(fn($g) => $g->count())
                ->keys()
                ->take(3)
                ->implode(', ');

            $rows->push([
                'no'              => $index++,
                'barangay_name'   => $barangay?->barangay_name ?? 'Unknown',
                'landmark'        => $barangay?->landmark      ?? '—',
                'minor'           => $minor,
                'serious'         => $serious,
                'dead'            => $dead,
                'total_incidents' => $items->count(),
                'top_incidents'   => $topIncidents,
            ]);
        }

        return $rows;
    }

    public function index(Request $request)
    {
        $rows = $this->aggregateReports($request);

        return Inertia::render('Reports/MonthlyReport', [
            'reportData' => $rows->values(),
            'filters'    => $request->only(['date_from', 'date_to']),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $rows    = $this->aggregateReports($request);
        $from    = $request->input('date_from', 'all');
        $to      = $request->input('date_to',   'all');
        $filename = "monthly-report_{$from}_to_{$to}.csv";

        return Excel::download(new MonthlyBarangayReportExport($rows), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        $rows  = $this->aggregateReports($request);
        $from  = $request->input('date_from', '—');
        $to    = $request->input('date_to',   '—');

        $pdf = Pdf::loadView('reports.monthly_report_pdf', [
            'rows'      => $rows,
            'date_from' => $from,
            'date_to'   => $to,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("monthly-report_{$from}_to_{$to}.pdf");
    }
}
