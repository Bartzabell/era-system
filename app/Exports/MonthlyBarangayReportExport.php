<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class MonthlyBarangayReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn($row) => [
            $row['no'],
            $row['barangay_name'],
            $row['landmark'],
            $row['minor'],
            $row['serious'],
            $row['dead'],
            $row['total_incidents'],
            $row['top_incidents'],
        ]);
    }

    public function headings(): array
    {
        return [
            'No.',
            'Barangay',
            'Landmark',
            'Medical Condition',
            'Minor',
            'Serious',
            'Dead',
            'Total No. of Incidents',
            'Incidents (Top 3)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 22,
            'C' => 25,
            'D' => 18,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 22,
            'I' => 45,
        ];
    }
}
