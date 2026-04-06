<?php

namespace App\Exports;

use App\Models\IncidentReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class MonthlyReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? Carbon::now()->month;
        $this->year = $year ?? Carbon::now()->year;
    }

    public function collection()
    {
        return IncidentReport::with(['incident', 'emergency', 'barangay', 'user'])
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Report ID',
            'Date & Time',
            'Incident Type',
            'Emergency Type',
            'Location (Barangay)',
            'Severity Level',
            'Status',
            'Casualty Count',
            'Reporter',
            'Responder Name',
            'Contact Number',
            'Plate Number',
            'Response Time (min)',
            'Distance (km)',
            'Remarks'
        ];
    }

    public function map($report): array
    {
        $responseTime = null;
        if ($report->datetime_arrived) {
            $created = Carbon::parse($report->created_at);
            $arrived = Carbon::parse($report->datetime_arrived);
            $responseTime = $created->diffInMinutes($arrived);
        }

        return [
            $report->id,
            Carbon::parse($report->created_at)->format('Y-m-d H:i:s'),
            $report->incident->incident_name ?? '',
            $report->emergency->emergency_name ?? '',
            $report->barangay->barangay_name ?? '',
            ucfirst($report->severity_level),
            ucfirst($report->status),
            $report->casualty_count ?? 0,
            $report->user->name ?? '',
            $report->responder_name ?? '',
            $report->responder_contact_no ?? '',
            $report->plate_no ?? '',
            $responseTime ?? '',
            $report->distance ?? '',
            $report->remarks ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y') . ' Report';
    }
}
