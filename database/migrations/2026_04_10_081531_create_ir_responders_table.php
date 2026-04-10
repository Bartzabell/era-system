<?php

use App\Models\IncidentReport;
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
        Schema::create('ir_responders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(IncidentReport::class, 'ir_id')->nullable();
            $table->foreignIdFor(User::class, 'responder_id')->nullable();
            $table->string('responder_name')->nullable();
            $table->string('responder_type')->nullable();
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
        Schema::dropIfExists('ir_responders');
    }
};
