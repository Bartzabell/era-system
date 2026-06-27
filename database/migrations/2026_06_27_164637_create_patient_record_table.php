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

            // Chart code e.g. PRC-2024-0001
            $table->string('chart_code')->nullable()->unique();

            // Patient info
            $table->string('patient_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();

            // Triage
            $table->enum('triage_category', ['red', 'yellow', 'green', 'black'])->nullable();

            // Clinical
            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();

            // Vital signs
            $table->string('bp')->nullable();           // e.g. "120/80"
            $table->unsignedSmallInteger('hr')->nullable();  // bpm
            $table->unsignedTinyInteger('rr')->nullable();   // breaths/min
            $table->decimal('temperature', 4, 1)->nullable(); // °C
            $table->unsignedTinyInteger('o2_sat')->nullable(); // %

            // Treatment
            $table->text('treatment_given')->nullable();

            // Disposition
            $table->enum('disposition', ['admitted', 'discharged', 'deceased', 'referred', 'treated_on_site'])->nullable();
            $table->text('disposition_remarks')->nullable();

            // Attending
            $table->string('attending_responder')->nullable();

            // Audit
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
