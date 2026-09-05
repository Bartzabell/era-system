<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PatientRecordChart;
use App\Models\IncidentReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientRecordChartController extends Controller
{
    private function hasFullAccess(): bool
    {
        $user = Auth::user();
        return $user->hasPermission('admin_access') || $user->hasPermission('responder_access');
    }

    /** Shared dropdown data passed to every Inertia render. */
    private function sharedData(): array
    {
        return [
            'incidentReports' => IncidentReport::select('id', 'incident_code', 'status')
                ->with(['barangay:id,barangay_name', 'incident:id,incident_name'])
                ->whereNull('deleted_at')
                ->latest()
                ->get()
                ->map(fn($r) => [
                    'id'    => $r->id,
                    'label' => "{$r->incident_code}",
                ]),
        ];
    }

    // ── Validation rules (shared between store & update) ───────────────────

    private function rules(): array
    {
        return [
            // Identifiers
            'incident_report_id'        => 'nullable|exists:incident_reports,id',
            'case_number'               => 'nullable|string|max:100',

            // Case header
            'case_date'                 => 'nullable|date',
            'case_type'                 => 'nullable|in:medical_case,trauma_case,vehicular_accident,patient_conduction,special_case',
            'tag'                       => 'nullable|string|max:100',

            // Times
            'time_dispatch'             => 'nullable|date_format:H:i,H:i:s',
            'time_arrived_on_scene'     => 'nullable|date_format:H:i,H:i:s',
            'time_enroute_to_hospital'  => 'nullable|date_format:H:i,H:i:s',
            'time_arrival_in_hospital'  => 'nullable|date_format:H:i,H:i:s',
            'time_departure_in_hospital'=> 'nullable|date_format:H:i,H:i:s',
            'time_back_to_base'         => 'nullable|date_format:H:i,H:i:s',

            // Mileage
            'mileage_before_run'        => 'nullable|numeric|min:0',
            'mileage_back_to_base'      => 'nullable|numeric|min:0',

            // Crew
            'dispatcher'                => 'nullable|string|max:255',
            'unit'                      => 'nullable|string|max:100',
            'transport_officer'         => 'nullable|string|max:255',
            'team_leader'               => 'nullable|string|max:255',
            'medics'                    => 'nullable|string|max:500',

            // Patient info
            'last_name'                 => 'nullable|string|max:100',
            'first_name'                => 'nullable|string|max:100',
            'middle_name'               => 'nullable|string|max:100',
            'patient_name'              => 'nullable|string|max:255',
            'age'                       => 'nullable|integer|min:0|max:150',
            'gender'                    => 'nullable|in:male,female',
            'civil_status'              => 'nullable|in:single,married,widowed',
            'address'                   => 'nullable|string',
            'informant_legal_guardian'  => 'nullable|string|max:255',
            'date_of_birth'             => 'nullable|date',
            'contact_number'            => 'nullable|string|max:30',
            'religion'                  => 'nullable|string|max:100',
            'insurance_hmo_provider'    => 'nullable|string|max:255',
            'insurance_hmo_number'      => 'nullable|string|max:100',
            'dnr'                       => 'nullable|boolean',

            // Primary assessment
            'mental_status'             => 'nullable|array',
            'mental_status.*'           => 'string|in:alert_and_oriented,to_pain,to_verbal_stimuli,unresponsive',
            'chief_complaint'           => 'nullable|string',
            'airway'                    => 'nullable|in:patent,aspiration_risk,secretions,suctioning_required',
            'breathing'                 => 'nullable|in:normal,dyspnea,retractions,accessory_muscle_use',
            'pulse'                     => 'nullable|in:regular,irregular,strong,weak',
            'skin_color'                => 'nullable|in:normal,paled,flushed,cyanotic,mottled',
            'skin_moisture'             => 'nullable|in:dry,moist,diaphoretic',
            'skin_temp'                 => 'nullable|in:normal,cool,hot',
            'capillary_refill'          => 'nullable|in:<2sec,>2sec',
            'pupil'                     => 'nullable|in:pearl,constricted,dilated,unequal',
            'stroke_signs'              => 'nullable|array',
            'stroke_signs.*'            => 'string|in:facial_droop,arm_drift,speech,time',
            'stroke_time'               => 'nullable|string|max:20',
            'interventions'             => 'nullable|array',
            'interventions.*'           => 'string',
            'oxygenation_lpm'           => 'nullable|string|max:20',
            'transport_priority'        => 'nullable|in:priority_1_critical,priority_2_emergent,priority_3_urgent,priority_4_non_urgent',

            // SAMPLE
            'sample_s'                  => 'nullable|string',
            'sample_a'                  => 'nullable|string',
            'sample_m'                  => 'nullable|string',
            'sample_p'                  => 'nullable|string',
            'sample_l'                  => 'nullable|string',
            'sample_e'                  => 'nullable|string',

            // Trauma
            'trauma_type'               => 'nullable|array',
            'trauma_type.*'             => 'string|in:vehicular_accident,trauma_of_other_cause',
            'dcapbtls'                  => 'nullable|array',
            'dcapbtls.*'                => 'string',

            // Vital signs
            'vital_signs_log'           => 'nullable|array',
            'vital_signs_log.*.time'    => 'nullable|string',
            'vital_signs_log.*.temp'    => 'nullable|numeric',
            'vital_signs_log.*.pulse'   => 'nullable|integer',
            'vital_signs_log.*.respiration' => 'nullable|integer',
            'vital_signs_log.*.bp'      => 'nullable|string',
            'vital_signs_log.*.gcs'     => 'nullable|integer',
            'bp'                        => 'nullable|string|max:20',
            'hr'                        => 'nullable|integer|min:0|max:300',
            'rr'                        => 'nullable|integer|min:0|max:100',
            'temperature'               => 'nullable|numeric|min:30|max:45',
            'o2_sat'                    => 'nullable|integer|min:0|max:100',

            // GCS
            'gcs_eye'                   => 'nullable|integer|min:1|max:4',
            'gcs_verbal'                => 'nullable|integer|min:1|max:5',
            'gcs_motor'                 => 'nullable|integer|min:1|max:6',
            'gcs_total'                 => 'nullable|integer|min:3|max:15',

            // Narrative
            'narrative_report'          => 'nullable|string',

            // Disposition
            'disposition'               => 'nullable|in:admitted,discharged,deceased,referred,treated_on_site,transported_to_hospital,released_with_treatment,endorsed_to_ems,transported_to_other',
            'disposition_remarks'       => 'nullable|string',
            'hospital_name'             => 'nullable|string|max:255',
            'hospital_address'          => 'nullable|string',
            'hospital_department'       => 'nullable|string|max:255',
            'advanced_call_by'          => 'nullable|string|max:255',
            'call_received_by'          => 'nullable|string|max:255',

            // Signatures
            'accomplished_endorsed_by'  => 'nullable|string|max:255',
            'noted_by'                  => 'nullable|string|max:255',
            'endorsement_received_by'   => 'nullable|string|max:255',

            // Extras
            'patient_valuables'         => 'nullable|string',
            'supplies_used'             => 'nullable|string',
            'human_error'               => 'nullable|string',
            'mechanical_error'          => 'nullable|string',
            'vehicle_types_involved'    => 'nullable|array',
            'vehicle_types_involved.*'  => 'string|in:two_wheels,three_wheels,four_wheels,six_wheels_and_up',

            // Clinical
            'attending_responder'       => 'nullable|string|max:255',
            'diagnosis'                 => 'nullable|string',
            'treatment_given'           => 'nullable|string',
            'triage_category'           => 'nullable|in:red,yellow,green,black',
        ];
    }

    private function fillableFields(): array
    {
        return [
            'incident_report_id', 'case_number',
            'case_date', 'case_type', 'tag',
            'time_dispatch', 'time_arrived_on_scene', 'time_enroute_to_hospital',
            'time_arrival_in_hospital', 'time_departure_in_hospital', 'time_back_to_base',
            'mileage_before_run', 'mileage_back_to_base',
            'dispatcher', 'unit', 'transport_officer', 'team_leader', 'medics',
            'last_name', 'first_name', 'middle_name', 'patient_name',
            'age', 'gender', 'civil_status', 'address', 'informant_legal_guardian',
            'date_of_birth', 'contact_number', 'religion',
            'insurance_hmo_provider', 'insurance_hmo_number', 'dnr',
            'mental_status', 'chief_complaint',
            'airway', 'breathing', 'pulse',
            'skin_color', 'skin_moisture', 'skin_temp',
            'capillary_refill', 'pupil',
            'stroke_signs', 'stroke_time',
            'interventions', 'oxygenation_lpm',
            'transport_priority',
            'sample_s', 'sample_a', 'sample_m', 'sample_p', 'sample_l', 'sample_e',
            'trauma_type', 'dcapbtls',
            'vital_signs_log', 'bp', 'hr', 'rr', 'temperature', 'o2_sat',
            'gcs_eye', 'gcs_verbal', 'gcs_motor', 'gcs_total',
            'narrative_report',
            'disposition', 'disposition_remarks',
            'hospital_name', 'hospital_address', 'hospital_department',
            'advanced_call_by', 'call_received_by',
            'accomplished_endorsed_by', 'noted_by', 'endorsement_received_by',
            'patient_valuables', 'supplies_used',
            'human_error', 'mechanical_error',
            'vehicle_types_involved',
            'attending_responder', 'diagnosis', 'treatment_given', 'triage_category',
        ];
    }

    // ── INDEX ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        $search  = $request->input('search', '');
        $tab     = $request->input('tab', 'all');

        $query = PatientRecordChart::with([
                'incidentReport:id,incident_code',
                'creator:id,first_name,last_name',
            ])
            ->when($search, fn($q) => $q->where(fn($inner) =>
                $inner->where('patient_name',      'like', "%{$search}%")
                      ->orWhere('chart_code',       'like', "%{$search}%")
                      ->orWhere('case_number',      'like', "%{$search}%")
                      ->orWhere('chief_complaint',  'like', "%{$search}%")
                      ->orWhere('attending_responder', 'like', "%{$search}%")
                      ->orWhere('last_name',        'like', "%{$search}%")
                      ->orWhere('first_name',       'like', "%{$search}%")
                      ->orWhereHas('incidentReport', fn($q) =>
                          $q->where('incident_code', 'like', "%{$search}%")
                      )
            ))
            ->when($tab !== 'all', fn($q) => $q->where('triage_category', $tab));

        if (!$this->hasFullAccess()) {
            $query->where('created_by', Auth::id());
        }

        return Inertia::render('PatientRecordChart/Index', [
            ...$this->sharedData(),
            'records'       => $query->latest()->paginate($perPage)->withQueryString(),
            'hasFullAccess' => $this->hasFullAccess(),
            'filters'       => $request->only('per_page', 'search', 'tab'),
        ]);
    }

    // ── STORE ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate($this->rules());

        DB::transaction(function () use ($request) {
            $data = $request->only($this->fillableFields());
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            // Sync patient_name from name parts if provided
            if (!empty($data['last_name']) || !empty($data['first_name'])) {
                $data['patient_name'] = trim(
                    ($data['last_name'] ?? '') . ', ' .
                    ($data['first_name'] ?? '') . ' ' .
                    ($data['middle_name'] ?? '')
                );
            }

            $record = PatientRecordChart::create($data);

            // Auto-generate chart code: PCR-{YEAR}-{PADDED_ID}
            $record->chart_code = 'PCR-' . now()->year . '-' . str_pad($record->id, 4, '0', STR_PAD_LEFT);
            $record->save();
        });

        return redirect()->route('patient-record-chart.index')
            ->with('success', 'Patient record created successfully.');
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────

    public function update(Request $request, PatientRecordChart $patientRecordChart)
    {
        if (!$this->hasFullAccess() && $patientRecordChart->created_by !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate($this->rules());

        DB::transaction(function () use ($request, $patientRecordChart) {
            $data = $request->only($this->fillableFields());
            $data['updated_by'] = Auth::id();

            if (!empty($data['last_name']) || !empty($data['first_name'])) {
                $data['patient_name'] = trim(
                    ($data['last_name'] ?? '') . ', ' .
                    ($data['first_name'] ?? '') . ' ' .
                    ($data['middle_name'] ?? '')
                );
            }

            $patientRecordChart->update($data);
        });

        return redirect()->route('patient-record-chart.index')
            ->with('success', 'Patient record updated successfully.');
    }

    // ── DESTROY ────────────────────────────────────────────────────────────

    public function destroy(PatientRecordChart $patientRecordChart)
    {
        if (!$this->hasFullAccess()) abort(403, 'Unauthorized.');

        DB::transaction(function () use ($patientRecordChart) {
            $patientRecordChart->update(['deleted_by' => Auth::id()]);
            $patientRecordChart->delete();
        });

        return redirect()->route('patient-record-chart.index')
            ->with('success', 'Patient record deleted successfully.');
    }

    public function print(PatientRecordChart $patientRecordChart, Request $request)
    {
        if (!$this->hasFullAccess() && $patientRecordChart->created_by !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $pdf = Pdf::loadView('reports.print', [
            'record' => $patientRecordChart,
        ])->setPaper('A4', 'portrait');

        $filename = 'PCR-' . $patientRecordChart->chart_code . '.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
