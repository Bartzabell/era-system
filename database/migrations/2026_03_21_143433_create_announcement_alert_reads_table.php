<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_alert_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_alert_id')->constrained('announcement_alerts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at')->useCurrent();
            $table->unique(['announcement_alert_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_alert_reads');
    }
};
