<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientRecordChart extends Model
{
    use SoftDeletes;

    protected $table = 'patient_record_charts';

    protected $fillable = [
        'incident_report_id',
        'chart_code',
        'patient_name',
        'age',
        'sex',
        'address',
        'triage_category',
        'chief_complaint',
        'diagnosis',
        'bp',
        'hr',
        'rr',
        'temperature',
        'o2_sat',
        'treatment_given',
        'disposition',
        'disposition_remarks',
        'attending_responder',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class, 'incident_report_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
