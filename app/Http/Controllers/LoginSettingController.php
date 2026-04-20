<?php

namespace App\Http\Controllers;

use App\Models\LoginSetting;
use App\Models\TermsSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginSettingController extends Controller
{
    /**
     * Show the settings editor page.
     */
    public function index()
    {
        $login = LoginSetting::allAsArray();
        $terms = TermsSetting::allAsArray();

        return Inertia::render('Admin/LoginSettings', [
            'loginSettings' => $login,
            'termsSettings' => $terms,
        ]);
    }

    /**
     * Update login page settings.
     */
    public function updateLogin(Request $request)
    {
        $validated = $request->validate([
            'auth_title'       => 'required|string|max:100',
            'auth_description' => 'required|string|max:200',
            'stat_1_value'     => 'required|string|max:50',
            'stat_1_label'     => 'required|string|max:80',
            'stat_2_value'     => 'required|string|max:50',
            'stat_2_label'     => 'required|string|max:80',
            'stat_3_value'     => 'required|string|max:50',
            'stat_3_label'     => 'required|string|max:80',
            'stat_4_value'     => 'required|string|max:50',
            'stat_4_label'     => 'required|string|max:80',
            'hero_title'       => 'required|string|max:200',
            'hero_subtitle'    => 'required|string|max:500',
            'hero_footer'      => 'required|string|max:300',
            'feature_cards'    => 'required|array|min:1|max:8',
            'feature_cards.*.icon'        => 'required|string',
            'feature_cards.*.title'       => 'required|string|max:80',
            'feature_cards.*.description' => 'required|string|max:200',
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'feature_cards') {
                LoginSetting::set('feature_cards', json_encode($value));
            } else {
                LoginSetting::set($key, $value);
            }
        }

        return back()->with('success', 'Login page settings updated.');
    }

    /**
     * Update Terms & Conditions settings.
     */
    public function updateTerms(Request $request)
    {
        $validated = $request->validate([
            'privacy_intro'          => 'required|string',
            'privacy_sections'       => 'required|array|min:1',
            'privacy_sections.*.title'   => 'required|string|max:150',
            'privacy_sections.*.content' => 'required|string',
            'privacy_checkbox_label' => 'required|string|max:500',
            'terms_intro'            => 'required|string',
            'terms_sections'         => 'required|array|min:1',
            'terms_sections.*.title'     => 'required|string|max:150',
            'terms_sections.*.content'   => 'required|string',
            'terms_checkbox_label'   => 'required|string|max:500',
        ]);

        TermsSetting::set('privacy_intro',          $validated['privacy_intro']);
        TermsSetting::set('privacy_sections',       json_encode($validated['privacy_sections']));
        TermsSetting::set('privacy_checkbox_label', $validated['privacy_checkbox_label']);
        TermsSetting::set('terms_intro',            $validated['terms_intro']);
        TermsSetting::set('terms_sections',         json_encode($validated['terms_sections']));
        TermsSetting::set('terms_checkbox_label',   $validated['terms_checkbox_label']);

        return back()->with('success', 'Terms & Conditions updated.');
    }
}
