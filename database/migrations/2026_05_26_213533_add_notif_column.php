<?php

use App\Models\IncidentReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcement_alerts', function (Blueprint $table) {
            $table->foreignIdFor(IncidentReport::class, 'incident_report_id')->nullable();
            $table->boolean('for_administrators')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('announcement_alerts', function (Blueprint $table) {
            $table->dropColumn(['incident_report_id', 'for_administrators']);
        });
    }
};
