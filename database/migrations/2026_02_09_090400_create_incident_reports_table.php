<?php

use App\Models\Barangay;
use App\Models\Emergency;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id')->nullable();
            $table->foreignIdFor(Barangay::class, 'barangay_id')->nullable();
            $table->text('map_coordinates')->nullable();
            $table->foreignIdFor(Emergency::class, 'emergency_id')->nullable();
            $table->foreignIdFor(Incident::class, 'incident_id')->nullable();
            $table->integer('casualty_count')->nullable();
            $table->integer('distance')->nullable();
            $table->text('attachment')->nullable();
            $table->string('responder_name')->nullable();
            $table->string('responder_contact_no')->nullable();
            $table->datetime('estimated_arrival')->nullable();
            $table->datetime('datetime_arrived')->nullable();
            $table->string('plate_no')->nullable();
            $table->string('status')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            $table->foreignIdFor(User::class, 'deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
