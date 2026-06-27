<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PatientRecordChart;
use App\Models\IncidentReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                    'label' => "{$r->incident_code} ",
                ]),
        ];
    }

    // ── INDEX ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        $search  = $request->input('search', '');
        $tab     = $request->input('tab', 'all'); // all | red | yellow | green | black

        $query = PatientRecordChart::with([
                'incidentReport:id,incident_code',
                'creator:id,first_name,last_name',
            ])
            ->when($search, fn($q) => $q->where(fn($inner) =>
                $inner->where('patient_name',  'like', "%{$search}%")
                      ->orWhere('chart_code',  'like', "%{$search}%")
                      ->orWhere('chief_complaint', 'like', "%{$search}%")
                      ->orWhere('attending_responder', 'like', "%{$search}%")
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
        $request->validate([
            'incident_report_id'  => 'nullable|exists:incident_reports,id',
            'patient_name'        => 'required|string|max:255',
            'age'                 => 'nullable|integer|min:0|max:150',
            'sex'                 => 'nullable|in:male,female,other',
            'address'             => 'nullable|string',
            'triage_category'     => 'nullable|in:red,yellow,green,black',
            'chief_complaint'     => 'nullable|string',
            'diagnosis'           => 'nullable|string',
            'bp'                  => 'nullable|string|max:20',
            'hr'                  => 'nullable|integer|min:0|max:300',
            'rr'                  => 'nullable|integer|min:0|max:100',
            'temperature'         => 'nullable|numeric|min:30|max:45',
            'o2_sat'              => 'nullable|integer|min:0|max:100',
            'treatment_given'     => 'nullable|string',
            'disposition'         => 'nullable|in:admitted,discharged,deceased,referred,treated_on_site',
            'disposition_remarks' => 'nullable|string',
            'attending_responder' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only([
                'incident_report_id', 'patient_name', 'age', 'sex', 'address',
                'triage_category', 'chief_complaint', 'diagnosis',
                'bp', 'hr', 'rr', 'temperature', 'o2_sat',
                'treatment_given', 'disposition', 'disposition_remarks',
                'attending_responder',
            ]);

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $record = PatientRecordChart::create($data);

            // Auto-generate chart code: PRC-{YEAR}-{PADDED_ID}
            $record->chart_code = 'PRC-' . now()->year . '-' . str_pad($record->id, 4, '0', STR_PAD_LEFT);
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

        $request->validate([
            'incident_report_id'  => 'nullable|exists:incident_reports,id',
            'patient_name'        => 'required|string|max:255',
            'age'                 => 'nullable|integer|min:0|max:150',
            'sex'                 => 'nullable|in:male,female,other',
            'address'             => 'nullable|string',
            'triage_category'     => 'nullable|in:red,yellow,green,black',
            'chief_complaint'     => 'nullable|string',
            'diagnosis'           => 'nullable|string',
            'bp'                  => 'nullable|string|max:20',
            'hr'                  => 'nullable|integer|min:0|max:300',
            'rr'                  => 'nullable|integer|min:0|max:100',
            'temperature'         => 'nullable|numeric|min:30|max:45',
            'o2_sat'              => 'nullable|integer|min:0|max:100',
            'treatment_given'     => 'nullable|string',
            'disposition'         => 'nullable|in:admitted,discharged,deceased,referred,treated_on_site',
            'disposition_remarks' => 'nullable|string',
            'attending_responder' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $patientRecordChart) {
            $data = $request->only([
                'incident_report_id', 'patient_name', 'age', 'sex', 'address',
                'triage_category', 'chief_complaint', 'diagnosis',
                'bp', 'hr', 'rr', 'temperature', 'o2_sat',
                'treatment_given', 'disposition', 'disposition_remarks',
                'attending_responder',
            ]);

            $data['updated_by'] = Auth::id();
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
}
