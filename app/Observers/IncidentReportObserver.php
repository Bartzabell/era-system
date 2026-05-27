<?php

namespace App\Observers;

use App\Models\AnnouncementAlert;
use App\Models\IncidentReport;

class IncidentReportObserver
{
    public function created(IncidentReport $incidentReport): void
    {
        $alreadyNotified = AnnouncementAlert::where('incident_report_id', $incidentReport->id)
            ->withTrashed()
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $incidentCode  = $incidentReport->incident_code ?? ('IR-' . $incidentReport->id);
        $priorityLabel = $incidentReport->priority_label ?? 'Unknown Priority';
        $location      = optional($incidentReport->siteLocation)->name
                         ?? $incidentReport->map_coordinates
                         ?? 'Unknown Location';
        $emergency = $incidentReport->emergency->emergency_name ?? '';
        $incident = $incidentReport->incident->incident_name ?? '';

        $title = "New Incident Report: {$incidentCode}";

        $message = "A new incident has been reported.\n"
            . "Code: {$incidentCode}\n"
            . "Priority: {$priorityLabel}\n"
            . "Location: {$location}\n"
            . "Emergency: {$emergency}\n"
            . "Incident: {$incident}\n";

        if ($incidentReport->casualty_count) {
            $message .= "\nCasualties: {$incidentReport->casualty_count}";
        }

        AnnouncementAlert::create([
            'incident_report_id'   => $incidentReport->id,
            'announcement_title'   => $title,
            'announcement_message' => $message,
            'for_citizens'         => false,
            'for_responders'       => false,
            'for_administrators'   => true,
            'created_by'           => $incidentReport->user_id ?? null,
            'updated_by'           => $incidentReport->user_id ?? null,
        ]);
    }
}
