<?php

namespace Database\Seeders;

use App\Models\IncidentReport;
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
                $incident_code,
                $user_id,
                $barangay_id,
                $emergency_id,
                $incident_id,
                $casualty_count,
                $minor_casualty_count,
                $serious_casualty_count,
                $deceased_casualty_count,
                $distance,
                $distance_km,
                $site_location_id,
                $map_coordinates,
                $remarks,
                $priority_score,
                $priority_level,
                $priority_label,
                $severity_level,
                $responder_count,
                $estimated_arrival,
                $datetime_arrived,
                $status,
                $reported_at,
            ] = $row;

            IncidentReport::updateOrCreate(
                ['incident_code' => $incident_code],
                [
                    'user_id'                  => $user_id,
                    'barangay_id'              => $barangay_id,
                    'emergency_id'             => $emergency_id,
                    'incident_id'              => $incident_id,
                    'map_coordinates'          => $map_coordinates,
                    'remarks'                  => $remarks,

                    'casualty_count'           => $casualty_count,
                    'minor_casualty_count'     => $minor_casualty_count,
                    'serious_casualty_count'   => $serious_casualty_count,
                    'deceased_casualty_count'  => $deceased_casualty_count,

                    'distance'                 => $distance,
                    'distance_km'              => $distance_km,
                    'site_location_id'         => $site_location_id,

                    'priority_score'           => $priority_score,
                    'priority_level'           => $priority_level,
                    'priority_label'           => $priority_label,
                    'severity_level'           => $severity_level,

                    'responder_count'          => $responder_count,
                    'estimated_arrival'        => $estimated_arrival ?: null,
                    'datetime_arrived'         => $datetime_arrived ?: null,
                    'status'                   => $status,

                    'reported_at'              => $reported_at,
                    'created_by'               => 1,
                    'updated_by'               => 1,
                ]
            );
        }

        fclose($handle);

        $this->command->info('IncidentReportImportSeeder completed — ' .
            IncidentReport::count() . ' records in table.');
    }
}
