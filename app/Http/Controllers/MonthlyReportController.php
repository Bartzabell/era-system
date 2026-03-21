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
    /**
     * Build the base query with date range and eager loads.
     */
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

    /**
     * Aggregate incident reports grouped by barangay.
     *
     * Returns a collection of rows with:
     *  - barangay_name
     *  - landmark
     *  - medical_condition: [minor, serious, dead]  (mapped from severity_level; no direct column → placeholder ??)
     *  - total_incidents
     *  - top_incidents  (up to 3, comma-separated)
     */
    private function aggregateReports(Request $request): \Illuminate\Support\Collection
    {
        $reports = $this->buildQuery($request)->get();

        // Group by barangay_id
        $grouped = $reports->groupBy('barangay_id');

        $rows = collect();
        $index = 1;

        foreach ($grouped as $barangayId => $items) {
            $first    = $items->first();
            $barangay = $first->barangay;

            // Medical condition counts — severity_level approximates medical condition.
            // 'low'    → minor
            // 'medium' → serious
            // 'high'   → dead/critical
            // The model has no explicit medical_condition column, so we derive from severity_level
            // and mark the raw column as "??" per requirement.
            $minor   = $items->where('severity_level', 'low')->count();
            $serious = $items->where('severity_level', 'medium')->count();
            $dead    = $items->where('severity_level', 'high')->count();

            // Top 3 incident names (by frequency)
            $topIncidents = $items
                ->groupBy(fn($r) => $r->incident?->incident_name ?? 'Unknown')
                ->sortByDesc(fn($g) => $g->count())
                ->keys()
                ->take(3)
                ->implode(', ');

            $rows->push([
                'no'             => $index++,
                'barangay_name'  => $barangay?->barangay_name ?? 'Unknown',
                'landmark'       => $barangay?->landmark    ?? '—',
                'medical_condition_raw' => '??',   // no direct DB column
                'minor'          => $minor,
                'serious'        => $serious,
                'dead'           => $dead,
                'total_incidents'=> $items->count(),
                'top_incidents'  => $topIncidents,
            ]);
        }

        return $rows;
    }

    /**
     * Display the monthly report page.
     */
    public function index(Request $request)
    {
        $rows = $this->aggregateReports($request);

        return Inertia::render('Reports/MonthlyReport', [
            'reportData' => $rows->values(),
            'filters'    => $request->only(['date_from', 'date_to']),
        ]);
    }

    /**
     * Export as CSV via Maatwebsite Excel.
     */
    public function exportCsv(Request $request)
    {
        $rows    = $this->aggregateReports($request);
        $from    = $request->input('date_from', 'all');
        $to      = $request->input('date_to',   'all');
        $filename = "monthly-report_{$from}_to_{$to}.csv";

        return Excel::download(new MonthlyBarangayReportExport($rows), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Print / download PDF via DomPDF.
     */
    public function exportPdf(Request $request)
    {
        $rows  = $this->aggregateReports($request);
        $from  = $request->input('date_from', '—');
        $to    = $request->input('date_to',   '—');

        $pdf = Pdf::loadView('reports.monthly_report_pdf', [
            'rows'      => $rows,
            'date_from' => $from,
            'date_to'   => $to,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("monthly-report_{$from}_to_{$to}.pdf");
    }
}
