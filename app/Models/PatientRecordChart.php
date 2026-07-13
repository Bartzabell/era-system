<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientRecordChart extends Model
{
    use SoftDeletes;

    protected $table = 'patient_record_charts';

    protected $fillable = [
        // Identifiers
        'incident_report_id',
        'chart_code',
        'case_number',

        // Case header
        'case_date',
        'case_type',
        'tag',

        // Times
        'time_dispatch',
        'time_arrived_on_scene',
        'time_enroute_to_hospital',
        'time_arrival_in_hospital',
        'time_departure_in_hospital',
        'time_back_to_base',

        // Mileage
        'mileage_before_run',
        'mileage_back_to_base',

        // Crew
        'dispatcher',
        'unit',
        'transport_officer',
        'team_leader',
        'medics',

        // Patient info
        'last_name',
        'first_name',
        'middle_name',
        'patient_name',
        'age',
        'gender',
        'civil_status',
        'address',
        'informant_legal_guardian',
        'date_of_birth',
        'contact_number',
        'religion',
        'insurance_hmo_provider',
        'insurance_hmo_number',
        'dnr',

        // Primary assessment
        'mental_status',
        'chief_complaint',
        'airway',
        'breathing',
        'pulse',
        'skin_color',
        'skin_moisture',
        'skin_temp',
        'capillary_refill',
        'pupil',
        'stroke_signs',
        'stroke_time',
        'interventions',
        'oxygenation_lpm',
        'transport_priority',

        // SAMPLE
        'sample_s',
        'sample_a',
        'sample_m',
        'sample_p',
        'sample_l',
        'sample_e',

        // Trauma
        'trauma_type',
        'dcapbtls',

        // Vital signs
        'vital_signs_log',
        'bp',
        'hr',
        'rr',
        'temperature',
        'o2_sat',

        // GCS
        'gcs_eye',
        'gcs_verbal',
        'gcs_motor',
        'gcs_total',

        // Narrative
        'narrative_report',

        // Disposition
        'disposition',
        'disposition_remarks',
        'hospital_name',
        'hospital_address',
        'hospital_department',
        'advanced_call_by',
        'call_received_by',

        // Signatures
        'accomplished_endorsed_by',
        'noted_by',
        'endorsement_received_by',

        // Extras
        'patient_valuables',
        'supplies_used',
        'human_error',
        'mechanical_error',
        'vehicle_types_involved',

        // Clinical
        'attending_responder',
        'diagnosis',
        'treatment_given',
        'triage_category',

        // Audit
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'mental_status'         => 'array',
        'stroke_signs'          => 'array',
        'interventions'         => 'array',
        'trauma_type'           => 'array',
        'dcapbtls'              => 'array',
        'vital_signs_log'       => 'array',
        'vehicle_types_involved'=> 'array',
        'dnr'                   => 'boolean',
        'case_date'             => 'date',
        'date_of_birth'         => 'date',
        'temperature'           => 'decimal:1',
    ];

    protected $appends = ['print_signed_url'];

    public function getPrintSignedUrlAttribute()
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'patient-record-chart.print-signed',
            now()->addMinutes(5),
            ['patientRecordChart' => $this->id]
        );
    }

    // ── Computed ───────────────────────────────────────────────────────────

    /** Full name from parts, falling back to patient_name. */
    public function getFullNameAttribute(): string
    {
        if ($this->last_name || $this->first_name) {
            return trim("{$this->last_name}, {$this->first_name} {$this->middle_name}");
        }
        return $this->patient_name ?? '';
    }

    /** Computed GCS total from components. */
    public function getGcsTotalComputedAttribute(): ?int
    {
        if ($this->gcs_eye && $this->gcs_verbal && $this->gcs_motor) {
            return $this->gcs_eye + $this->gcs_verbal + $this->gcs_motor;
        }
        return null;
    }

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
