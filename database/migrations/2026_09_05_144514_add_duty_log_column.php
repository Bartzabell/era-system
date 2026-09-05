<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duty_logs', function (Blueprint $table) {
            $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');
            $table->dropUnique(['user_id', 'duty_date']);
        });
    }

    public function down(): void
    {
        Schema::table('duty_logs', function (Blueprint $table) {
            $table->dropColumn('checked_out_at');
            $table->unique(['user_id', 'duty_date']);
        });
    }
};