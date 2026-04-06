<?php

namespace App\Exports;

use App\Models\IncidentReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class CitizenReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? Carbon::now()->startOfMonth();
        $this->endDate = $endDate ?? Carbon::now()->endOfMonth();
    }

    public function collection()
    {
        return IncidentReport::with(['incident', 'emergency', 'barangay', 'user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNull('deleted_at')
            ->whereNotNull('user_id') // Only citizen-reported incidents
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Report ID',
            'Citizen Name',
            'Date Reported',
            'Time Reported',
            'Incident Type',
            'Emergency Type',
            'Location',
            'Coordinates',
            'Severity',
            'Casualty Count',
            'Status',
            'Date Resolved',
            'Response Time (min)',
            'Remarks'
        ];
    }

    public function map($report): array
    {
        $responseTime = null;
        $dateResolved = null;

        if ($report->datetime_arrived) {
            $created = Carbon::parse($report->created_at);
            $arrived = Carbon::parse($report->datetime_arrived);
            $responseTime = $created->diffInMinutes($arrived);
        }

        if (in_array($report->status, ['resolved', 'completed'])) {
            $dateResolved = Carbon::parse($report->updated_at)->format('Y-m-d H:i:s');
        }

        $coordinates = '';
        if ($report->map_coordinates) {
            $coords = json_decode($report->map_coordinates, true);
            if ($coords && isset($coords['lat']) && isset($coords['lng'])) {
                $coordinates = $coords['lat'] . ', ' . $coords['lng'];
            }
        }

        return [
            $report->id,
            $report->user->name ?? 'Anonymous',
            Carbon::parse($report->created_at)->format('Y-m-d'),
            Carbon::parse($report->created_at)->format('H:i:s'),
            $report->incident->incident_name ?? '',
            $report->emergency->emergency_name ?? '',
            $report->barangay->barangay_name ?? '',
            $coordinates,
            ucfirst($report->severity_level),
            $report->casualty_count ?? 0,
            ucfirst($report->status),
            $dateResolved ?? 'Ongoing',
            $responseTime ?? '',
            $report->remarks ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Citizen Reports';
    }
}
