<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_record_charts', function (Blueprint $table) {
            $table->id();

            // Link to incident report (optional)
            $table->foreignId('incident_report_id')
                  ->nullable()
                  ->constrained('incident_reports')
                  ->nullOnDelete();

            // Chart / case code
            $table->string('chart_code')->nullable()->unique();
            $table->string('case_number')->nullable();

            // ── CASE HEADER ───────────────────────────────────────────────
            $table->date('case_date')->nullable();
            $table->enum('case_type', [
                'medical_case',
                'trauma_case',
                'vehicular_accident',
                'patient_conduction',
                'special_case',
            ])->nullable();
            $table->string('tag')->nullable();                       // triage tag color/label on scene

            // Times
            $table->time('time_dispatch')->nullable();
            $table->time('time_arrived_on_scene')->nullable();
            $table->time('time_enroute_to_hospital')->nullable();
            $table->time('time_arrival_in_hospital')->nullable();
            $table->time('time_departure_in_hospital')->nullable();
            $table->time('time_back_to_base')->nullable();

            // Mileage
            $table->decimal('mileage_before_run', 8, 2)->nullable();  // kms
            $table->decimal('mileage_back_to_base', 8, 2)->nullable(); // kms

            // ── CREW ON DUTY ──────────────────────────────────────────────
            $table->string('dispatcher')->nullable();
            $table->string('unit')->nullable();
            $table->string('transport_officer')->nullable();
            $table->string('team_leader')->nullable();
            $table->string('medics')->nullable();

            // ── PATIENT INFO ──────────────────────────────────────────────
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('patient_name')->nullable();                          // kept for backward compat / search
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('civil_status', ['single', 'married', 'widowed'])->nullable();
            $table->text('address')->nullable();
            $table->string('informant_legal_guardian')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('religion')->nullable();
            $table->string('insurance_hmo_provider')->nullable();
            $table->string('insurance_hmo_number')->nullable();
            $table->boolean('dnr')->nullable();                      // Do Not Resuscitate

            // ── PRIMARY ASSESSMENT ────────────────────────────────────────
            // Mental status (stored as array of checked values)
            $table->json('mental_status')->nullable();
            // e.g. ["alert_and_oriented","to_pain","to_verbal_stimuli","unresponsive"]

            $table->text('chief_complaint')->nullable();

            // Airway / Breathing / Pulse  (store selected option as string)
            $table->string('airway')->nullable();
            // patent | aspiration_risk | secretions | suctioning_required
            $table->string('breathing')->nullable();
            // normal | dyspnea | retractions | accessory_muscle_use
            $table->string('pulse')->nullable();
            // regular | irregular | strong | weak

            // Skin
            $table->string('skin_color')->nullable();
            // normal | paled | flushed | cyanotic | mottled
            $table->string('skin_moisture')->nullable();
            // dry | moist | diaphoretic
            $table->string('skin_temp')->nullable();
            // normal | cool | hot

            // Capillary refill
            $table->string('capillary_refill')->nullable();          // <2sec | >2sec

            // Pupil
            $table->string('pupil')->nullable();
            // pearl | constricted | dilated | unequal

            // Stroke signs (json array of checked signs)
            $table->json('stroke_signs')->nullable();
            // e.g. ["facial_droop","arm_drift","speech","time"]
            $table->string('stroke_time')->nullable();               // time noted for stroke sign

            // Interventions done (json array of checked items)
            $table->json('interventions')->nullable();
            /*
              artificial_airway, abdominal_thrust, bandaging, bleeding_control,
              bp_monitoring, cardiac_monitoring, cold_hot_application, cpr,
              burn_care, cervical_collar, assisting_on_medication,
              wound_care, suctioning, splinting_traction, defibrillation,
              spine_immobilization, vs_check, rescue_breathing,
              oxygenation_lpm, bvm, mask, nrb, nc, extrication
            */
            $table->string('oxygenation_lpm')->nullable();           // LPM value when oxygenation checked

            // Transport priority
            $table->enum('transport_priority', [
                'priority_1_critical',
                'priority_2_emergent',
                'priority_3_urgent',
                'priority_4_non_urgent',
            ])->nullable();

            // ── SECONDARY ASSESSMENT — SAMPLE ────────────────────────────
            $table->text('sample_s')->nullable();   // Signs & Symptoms
            $table->text('sample_a')->nullable();   // Allergies
            $table->text('sample_m')->nullable();   // Medications
            $table->text('sample_p')->nullable();   // Pertinent Past History
            $table->text('sample_l')->nullable();   // Last Oral Intake
            $table->text('sample_e')->nullable();   // Events leading to illness/injury

            // Trauma case sub-type
            $table->json('trauma_type')->nullable();
            // e.g. ["vehicular_accident","trauma_of_other_cause"]

            // DCAPBTLS (json array of checked findings)
            $table->json('dcapbtls')->nullable();
            /*
              deformity, contusion_concussion, abrasion,
              puncture_penetrating_wound, burn, tenderness, laceration,
              swelling, open_fracture, closed_fracture, dislocation,
              sprain_strain, alcohol_intoxication, gunshot_wound,
              animal_bite, hit_and_run, drowning, electrocution,
              mauling, stab_wound, fall
            */

            // ── VITAL SIGNS (time-series rows stored as JSON) ─────────────
            // Each entry: { time, temp, pulse, respiration, bp, gcs }
            $table->json('vital_signs_log')->nullable();

            // Single snapshot vitals (kept for backward compat)
            $table->string('bp')->nullable();
            $table->unsignedSmallInteger('hr')->nullable();
            $table->unsignedTinyInteger('rr')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedTinyInteger('o2_sat')->nullable();

            // ── GLASGOW COMA SCALE ────────────────────────────────────────
            $table->unsignedTinyInteger('gcs_eye')->nullable();      // 1-4
            $table->unsignedTinyInteger('gcs_verbal')->nullable();   // 1-5
            $table->unsignedTinyInteger('gcs_motor')->nullable();    // 1-6
            $table->unsignedTinyInteger('gcs_total')->nullable();    // 3-15

            // ── NARRATIVE ─────────────────────────────────────────────────
            $table->text('narrative_report')->nullable();

            // ── DISPOSITION ───────────────────────────────────────────────
            $table->enum('disposition', [
                'admitted',
                'discharged',
                'deceased',
                'referred',
                'treated_on_site',
                'transported_to_hospital',
                'released_with_treatment',
                'endorsed_to_ems',
                'transported_to_other',
            ])->nullable();
            $table->text('disposition_remarks')->nullable();

            // Hospital
            $table->string('hospital_name')->nullable();
            $table->text('hospital_address')->nullable();
            $table->string('hospital_department')->nullable();
            $table->string('advanced_call_by')->nullable();
            $table->string('call_received_by')->nullable();

            // ── SIGNATURES ────────────────────────────────────────────────
            $table->string('accomplished_endorsed_by')->nullable();
            $table->string('noted_by')->nullable();
            $table->string('endorsement_received_by')->nullable();

            // ── PATIENT VALUABLES & SUPPLIES ─────────────────────────────
            $table->text('patient_valuables')->nullable();
            $table->text('supplies_used')->nullable();

            // ── ERRORS ───────────────────────────────────────────────────
            $table->text('human_error')->nullable();
            $table->text('mechanical_error')->nullable();

            // ── VEHICLE TYPES INVOLVED ────────────────────────────────────
            $table->json('vehicle_types_involved')->nullable();
            // e.g. ["two_wheels","four_wheels"]

            // ── ATTENDING ─────────────────────────────────────────────────
            $table->string('attending_responder')->nullable();

            // ── DIAGNOSIS (clinical impression) ───────────────────────────
            $table->text('diagnosis')->nullable();
            $table->text('treatment_given')->nullable();

            // ── TRIAGE CATEGORY (MDRRMO internal) ────────────────────────
            $table->enum('triage_category', ['red', 'yellow', 'green', 'black'])->nullable();

            // ── AUDIT ─────────────────────────────────────────────────────
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_record_charts');
    }
};
