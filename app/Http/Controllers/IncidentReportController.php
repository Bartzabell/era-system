<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IncidentReport;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $incidentReports = IncidentReport::with([
            'user:id,first_name,last_name,full_name',
            'barangay:id,barangay_name',
            'incident:id,incident_name,severity_level',
            'emergency:id,emergency_name,severity_level'
        ])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($report) {
            return [
                'id' => $report->id,
                'user_name' => $report->user ? $report->user->full_name : 'N/A',
                'barangay' => $report->barangay ? $report->barangay->barangay_name : 'N/A',
                'incident' => $report->incident ? $report->incident->incident_name : 'N/A',
                'emergency' => $report->emergency ? $report->emergency->emergency_name : 'N/A',
                'severity_level' => $report->severity_level,
                'casualty_count' => $report->casualty_count ?? 0,
                'responder_name' => $report->responder_name,
                'responder_contact_no' => $report->responder_contact_no,
                'plate_no' => $report->plate_no,
                'status' => $report->status,
                'estimated_arrival' => $report->estimated_arrival,
                'datetime_arrived' => $report->datetime_arrived,
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('AccidentReport/Index', [
            'incidentReports' => $incidentReports,
        ]);
    }
}
