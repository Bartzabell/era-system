<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Seed defaults
        $defaults = [
            // Left panel (auth box)
            'auth_title'       => 'Welcome Back!',
            'auth_description' => 'Sign in to your account to continue',
            'stat_1_value'     => '24/7',
            'stat_1_label'     => 'Always on',
            'stat_2_value'     => '4',
            'stat_2_label'     => 'Major Emergency Type',
            'stat_3_value'     => 'GEARS',
            'stat_3_label'     => 'Mobile App',
            'stat_4_value'     => 'MDRRMO',
            'stat_4_label'     => 'Centralized System',

            // Right panel (hero)
            'hero_title'       => "Reliable Response,\nReliable Protection",
            'hero_subtitle'    => "Join the GEARS network and help us build safer, more resilient\ncommunities through coordinated emergency response.",
            'hero_footer'      => 'Powered by GMA Cavite MDRRMO - Serving all 55 barangays of General Mariano Alvarez',

            // Feature cards (JSON array)
            'feature_cards'    => json_encode([
                ['icon' => 'PhPhone',         'title' => 'One-tap reporting',        'description' => 'Report any emergency in seconds using just a photo and your location'],
                ['icon' => 'PhBell',          'title' => 'Instant Alerts',           'description' => 'Community-wide notifications keep all GMA residents informed and prepared'],
                ['icon' => 'PhMapPin',        'title' => 'Live Incident Map',        'description' => 'Track emergencies and responders location across GMA'],
                ['icon' => 'PhClipboardText', 'title' => 'Priority-ranked Incidents','description' => 'Incidents are ranked using AHP-TOPSIS scoring'],
            ]),
        ];

        foreach ($defaults as $key => $value) {
            DB::table('login_settings')->insert(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_settings');
    }
};
