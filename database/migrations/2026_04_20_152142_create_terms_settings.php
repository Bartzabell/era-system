<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            // Privacy Policy
            'privacy_intro' => 'Please read our privacy policy before using <strong>GEARS</strong> (General Emergency Alert and Response System). Your data is used solely for emergency response purposes.',
            'privacy_sections' => json_encode([
                ['title' => '1. Data we collect',    'content' => 'GEARS collects your name, contact number, home address within General Mariano Alvarez (GMA), Cavite, and GPS location at the time of emergency reporting. We also collect photos or videos you submit as part of an emergency report.'],
                ['title' => '2. How we use your data','content' => 'Your personal information is used exclusively to facilitate emergency dispatch and response by the GMA Cavite MDRRMO. Your location data is shared in real-time with authorized dispatchers only during an active report.'],
                ['title' => '3. Data sharing',       'content' => 'We do not sell, trade, or share your personal data with third parties. Data may be shared with relevant government agencies (BFP, PNP, NDRRMC) when required by law or for official emergency coordination purposes.'],
                ['title' => '4. Data retention',     'content' => 'Emergency report data is retained for a minimum of 5 years in compliance with local government records retention policies. You may request access to your personal data by contacting the GMA MDRRMO office.'],
                ['title' => '5. Your rights',        'content' => 'Under the Data Privacy Act of 2012 (RA 10173), you have the right to access, correct, and object to the processing of your personal data. Contact the GMA MDRRMO Data Privacy Officer for any concerns.'],
            ]),
            'privacy_checkbox_label' => 'I have read and agree to the <strong>Privacy Policy</strong> of GEARS — GMA Cavite Emergency Response System.',

            // Terms of Use
            'terms_intro' => 'By using <strong>GEARS</strong>, you agree to use this app solely for genuine emergencies within General Mariano Alvarez (GMA), Cavite.',
            'terms_sections' => json_encode([
                ['title' => '1. Eligibility',                  'content' => 'GEARS is available to all registered residents of General Mariano Alvarez (GMA), Cavite. Users must be 18 years of age or older to register independently. Users below 18 years old are not permitted to create their own account and must be registered under the account of a parent or guardian who is a resident of GMA, Cavite and is 18 years of age or older.'],
                ['title' => '2. Age requirement',              'content' => 'By registering, you confirm that you are at least 18 years old. If you are below 18, your parent or legal guardian must complete the registration on your behalf using their own personal information and contact number. The account will be held in the name of the parent or guardian, who will be fully responsible for all activity and reports submitted through it.'],
                ['title' => '3. Residency requirement',        'content' => 'GEARS is exclusively for residents of General Mariano Alvarez (GMA), Cavite. You confirm that you currently reside within GMA at the time of registration. The MDRRMO reserves the right to verify your identity, age, and address before activating your account. Non-residents are not eligible to register.'],
                ['title' => '4. Proper Use',                   'content' => 'This app is strictly for reporting genuine emergencies occurring within or near GMA, Cavite. Filing false, misleading, or prank emergency reports is a serious violation of this agreement and may result in account suspension and legal action under the Revised Penal Code and applicable local ordinances of GMA, Cavite.'],
                ['title' => '5. Account responsibility',       'content' => 'You are solely responsible for all activity conducted under your registered account. Do not share your login credentials, OTP codes, or registered phone number with unauthorized individuals. If the account is registered on behalf of a minor, the parent or guardian account holder is fully and legally responsible for all reports and actions submitted through the account.'],
                ['title' => '6. Parental and guardian consent','content' => 'If you are registering on behalf of a minor (below 18 years old), you confirm that you are the legal parent or guardian of that minor, that you reside in GMA Cavite, and that you give your full and informed consent for the minor to use GEARS under your registered account and supervision.'],
                ['title' => '7. Limitation of liability',      'content' => 'GEARS and the GMA Cavite MDRRMO are not liable for delays in emergency response caused by inaccurate location data, poor network connection, device failure, or force majeure events beyond the control of the system or its operators.'],
            ]),
            'terms_checkbox_label' => 'I agree to the <strong>Terms of Use</strong> of GEARS and confirm that I meet the age and residency requirements, and that I will only use this app to report genuine emergencies in GMA, Cavite.',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('terms_settings')->insert(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_settings');
    }
};
