<?php

namespace App\Http\Controllers;

use App\Models\LoginSetting;
use App\Models\TermsSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class LoginController extends Controller
{
    /**
     * Render the login page with all customizable content as props.
     */
    public function create()
    {
        $login = LoginSetting::allAsArray();
        $terms = TermsSetting::allAsArray();

        // Decode JSON fields
        $featureCards   = isset($login['feature_cards'])    ? json_decode($login['feature_cards'],    true) : [];
        $privacySections = isset($terms['privacy_sections']) ? json_decode($terms['privacy_sections'], true) : [];
        $termsSections   = isset($terms['terms_sections'])   ? json_decode($terms['terms_sections'],   true) : [];

        return Inertia::render('auth/Login', [
            'canResetPassword' => true,
            'canRegister'      => Features::enabled(Features::registration()),

            // Auth box (left panel)
            'authTitle'       => $login['auth_title']       ?? 'Welcome Back!',
            'authDescription' => $login['auth_description'] ?? 'Sign in to your account to continue',
            'stat1Value'      => $login['stat_1_value']     ?? '24/7',
            'stat1Label'      => $login['stat_1_label']     ?? 'Always on',
            'stat2Value'      => $login['stat_2_value']     ?? '4',
            'stat2Label'      => $login['stat_2_label']     ?? 'Major Emergency Type',
            'stat3Value'      => $login['stat_3_value']     ?? 'GEARS',
            'stat3Label'      => $login['stat_3_label']     ?? 'Mobile App',
            'stat4Value'      => $login['stat_4_value']     ?? 'MDRRMO',
            'stat4Label'      => $login['stat_4_label']     ?? 'Centralized System',

            // Hero (right panel)
            'heroTitle'    => $login['hero_title']    ?? "Reliable Response,\nReliable Protection",
            'heroSubtitle' => $login['hero_subtitle'] ?? '',
            'heroFooter'   => $login['hero_footer']   ?? '',
            'featureCards' => $featureCards,

            // Terms
            'termsPrivacyIntro'       => $terms['privacy_intro']          ?? '',
            'termsPrivacySections'    => $privacySections,
            'termsPrivacyCheckbox'    => $terms['privacy_checkbox_label'] ?? '',
            'termsIntro'              => $terms['terms_intro']            ?? '',
            'termsSections'           => $termsSections,
            'termsCheckbox'           => $terms['terms_checkbox_label']   ?? '',
        ]);
    }
}
