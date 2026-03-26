<?php

namespace Database\Seeders;

use App\Models\IncidentReport;
use App\Models\Incident;
use Illuminate\Database\Seeder;

class IncidentReportImportSeeder extends Seeder
{
    public function run(): void
    {
        $file   = base_path('database/seeders/data/incident_reports_import.csv');
        $handle = fopen($file, 'r');

        // Skip header
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            [
                $user_id, $barangay_id, $emergency_id, $incident_id,
                $casualty_count, $distance, $map_coordinates, $remarks,
                $priority_score, $priority_level, $priority_label, $reported_at  // 👈 add here
            ] = $row;

            $incident = Incident::find($incident_id);
            $severity = match(true) {
                ($incident?->base_severity ?? 0) >= 7 => 'high',
                ($incident?->base_severity ?? 0) >= 4 => 'medium',
                default                               => 'low',
            };

            IncidentReport::create([
                'user_id'         => $user_id,
                'barangay_id'     => $barangay_id,
                'emergency_id'    => $emergency_id,
                'incident_id'     => $incident_id,
                'casualty_count'  => $casualty_count,
                'distance'        => $distance,
                'map_coordinates' => $map_coordinates,
                'remarks'         => $remarks,
                'priority_score'  => $priority_score,
                'priority_level'  => $priority_level,
                'priority_label'  => $priority_label,
                'severity_level'  => $severity,
                'status'          => 'waiting',
                'reported_at'     => $reported_at,  // 👈 add here
                'created_by'      => 1,
                'updated_by'      => 1,
            ]);
        }

        fclose($handle);
    }
}

