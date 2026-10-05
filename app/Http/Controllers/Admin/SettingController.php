<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllSettings();
        $isSuperAdmin = Auth::user() && Auth::user()->isSuperAdmin();
        return view('admin.settings.index', compact('settings', 'isSuperAdmin'));
    }

    public function update(Request $request)
    {
        $isSuperAdmin = Auth::user() && Auth::user()->isSuperAdmin();

        $rules = [
            'theme_primary_color' => 'required|string|max:20',
            'theme_hover_color' => 'required|string|max:20',
            'btn_primary_bg' => 'required|string|max:20',
            'btn_primary_text' => 'required|string|max:20',
            'btn_primary_hover' => 'required|string|max:20',
            'btn_accent_bg' => 'required|string|max:20',
            'btn_accent_text' => 'required|string|max:20',
            'sidebar_bg_color' => 'required|string|max:20',
            'sidebar_active_color' => 'required|string|max:20',
            'sidebar_text_color' => 'nullable|string|max:20',
            'footer_copyright_prefix' => 'nullable|string|max:100',
            'footer_creator_name' => 'nullable|string|max:100',
            'footer_creator_url' => 'nullable|url|max:255',
            'theme_mode' => 'nullable|in:light,dark,system',
            'store_name' => 'nullable|string|max:60',
        ];

        // Frontend Web Colors (Light + Dark) - Only for Super Admin
        if ($isSuperAdmin) {
            $frontFields = [
                // Light
                'front_brand_primary', 'front_brand_hover', 'front_brand_light', 'front_accent_color',
                'front_btn_primary_bg', 'front_btn_primary_text', 'front_btn_primary_hover',
                'front_btn_accent_bg', 'front_btn_accent_text',
                'front_topbar_bg', 'front_topbar_text', 'front_header_bg',
                'front_body_bg', 'front_card_bg', 'front_card_border',
                'front_footer_bg', 'front_footer_text',
                'front_text_primary', 'front_text_muted',
                'front_input_bg', 'front_input_border', 'front_dropdown_bg',
                // Dark
                'front_dark_brand_primary', 'front_dark_brand_hover', 'front_dark_brand_light', 'front_dark_accent_color',
                'front_dark_btn_primary_bg', 'front_dark_btn_primary_text', 'front_dark_btn_primary_hover',
                'front_dark_body_bg', 'front_dark_card_bg', 'front_dark_card_border',
                'front_dark_header_bg', 'front_dark_topbar_bg', 'front_dark_topbar_text',
                'front_dark_footer_bg', 'front_dark_footer_text',
                'front_dark_text_primary', 'front_dark_text_muted',
                'front_dark_input_bg', 'front_dark_input_border', 'front_dark_dropdown_bg',
            ];

            foreach ($frontFields as $ff) {
                $rules[$ff] = 'nullable|string|max:30';
            }
        }

        $request->validate($rules);

        // Save Admin Settings
        Setting::set('theme_primary_color', $request->theme_primary_color, 'theme');
        Setting::set('theme_hover_color', $request->theme_hover_color, 'theme');
        Setting::set('btn_primary_bg', $request->btn_primary_bg, 'theme');
        Setting::set('btn_primary_text', $request->btn_primary_text, 'theme');
        Setting::set('btn_primary_hover', $request->btn_primary_hover, 'theme');
        Setting::set('btn_accent_bg', $request->btn_accent_bg, 'theme');
        Setting::set('btn_accent_text', $request->btn_accent_text, 'theme');
        Setting::set('sidebar_bg_color', $request->sidebar_bg_color, 'theme');
        Setting::set('sidebar_active_color', $request->sidebar_active_color, 'theme');
        Setting::set('sidebar_text_color', $request->sidebar_text_color ?? '#ffffff', 'theme');
        Setting::set('footer_copyright_prefix', $request->footer_copyright_prefix ?? '© 2026, made with ❤️ by', 'footer');
        Setting::set('footer_creator_name', $request->footer_creator_name ?? 'Decent Infoways', 'footer');
        Setting::set('footer_creator_url', $request->footer_creator_url ?? 'https://decentinfoways.com', 'footer');
        Setting::set('store_name', $request->store_name ?: 'Fresh Express', 'general');

        if ($request->filled('theme_mode')) {
            Setting::set('theme_mode', $request->theme_mode, 'theme');
        }

        // Save Frontend Web Settings if Super Admin
        if ($isSuperAdmin) {
            // Light Mode fields
            $lightKeys = [
                'front_brand_primary', 'front_brand_hover', 'front_brand_light', 'front_accent_color',
                'front_btn_primary_bg', 'front_btn_primary_text', 'front_btn_primary_hover',
                'front_btn_accent_bg', 'front_btn_accent_text',
                'front_topbar_bg', 'front_topbar_text', 'front_header_bg',
                'front_body_bg', 'front_card_bg', 'front_card_border',
                'front_footer_bg', 'front_footer_text',
                'front_text_primary', 'front_text_muted',
                'front_input_bg', 'front_input_border', 'front_dropdown_bg',
            ];
            foreach ($lightKeys as $lk) {
                if ($request->has($lk)) {
                    Setting::set($lk, $request->input($lk), 'front_theme');
                }
            }

            // Dark Mode fields
            $darkKeys = [
                'front_dark_brand_primary', 'front_dark_brand_hover', 'front_dark_brand_light', 'front_dark_accent_color',
                'front_dark_btn_primary_bg', 'front_dark_btn_primary_text', 'front_dark_btn_primary_hover',
                'front_dark_body_bg', 'front_dark_card_bg', 'front_dark_card_border',
                'front_dark_header_bg', 'front_dark_topbar_bg', 'front_dark_topbar_text',
                'front_dark_footer_bg', 'front_dark_footer_text',
                'front_dark_text_primary', 'front_dark_text_muted',
                'front_dark_input_bg', 'front_dark_input_border', 'front_dark_dropdown_bg',
            ];
            foreach ($darkKeys as $dk) {
                if ($request->has($dk)) {
                    Setting::set($dk, $request->input($dk), 'front_dark_theme');
                }
            }
        }

        return redirect()->route('admin.settings.index')->with('success', 'Centralized color and theme settings saved successfully!');
    }

    public function reset(Request $request)
    {
        $scope = $request->query('scope', 'all'); // 'all', 'admin', 'frontend'

        // Admin defaults
        if (in_array($scope, ['all', 'admin'])) {
            Setting::set('theme_primary_color', '#0f172a', 'theme');
            Setting::set('theme_hover_color', '#334155', 'theme');
            Setting::set('btn_primary_bg', '#0f172a', 'theme');
            Setting::set('btn_primary_text', '#ffffff', 'theme');
            Setting::set('btn_primary_hover', '#1e293b', 'theme');
            Setting::set('btn_accent_bg', '#10b981', 'theme');
            Setting::set('btn_accent_text', '#ffffff', 'theme');
            Setting::set('sidebar_bg_color', '#000000', 'theme');
            Setting::set('sidebar_active_color', '#add8e6', 'theme');
            Setting::set('sidebar_text_color', '#ffffff', 'theme');
            Setting::set('footer_copyright_prefix', '© 2026, made with ❤️ by', 'footer');
            Setting::set('footer_creator_name', 'Decent Infoways', 'footer');
            Setting::set('footer_creator_url', 'https://decentinfoways.com', 'footer');
            Setting::set('theme_mode', 'system', 'theme');
            Setting::set('store_name', 'Fresh Express', 'general');
        }

        // Frontend defaults (Light + Dark)
        if (in_array($scope, ['all', 'frontend']) && Auth::user() && Auth::user()->isSuperAdmin()) {
            // Frontend Light
            Setting::set('front_brand_primary', '#059669', 'front_theme');
            Setting::set('front_brand_hover', '#047857', 'front_theme');
            Setting::set('front_brand_light', '#ecfdf5', 'front_theme');
            Setting::set('front_accent_color', '#10b981', 'front_theme');
            Setting::set('front_btn_primary_bg', '#059669', 'front_theme');
            Setting::set('front_btn_primary_text', '#ffffff', 'front_theme');
            Setting::set('front_btn_primary_hover', '#047857', 'front_theme');
            Setting::set('front_btn_accent_bg', '#10b981', 'front_theme');
            Setting::set('front_btn_accent_text', '#ffffff', 'front_theme');
            Setting::set('front_topbar_bg', '#064e3b', 'front_theme');
            Setting::set('front_topbar_text', '#ecfdf5', 'front_theme');
            Setting::set('front_header_bg', '#ffffff', 'front_theme');
            Setting::set('front_body_bg', '#f6f8f7', 'front_theme');
            Setting::set('front_card_bg', '#ffffff', 'front_theme');
            Setting::set('front_card_border', '#e2e8f0', 'front_theme');
            Setting::set('front_footer_bg', '#0f172a', 'front_theme');
            Setting::set('front_footer_text', '#94a3b8', 'front_theme');
            Setting::set('front_text_primary', '#0f172a', 'front_theme');
            Setting::set('front_text_muted', '#64748b', 'front_theme');
            Setting::set('front_input_bg', '#ffffff', 'front_theme');
            Setting::set('front_input_border', '#cbd5e1', 'front_theme');
            Setting::set('front_dropdown_bg', '#ffffff', 'front_theme');

            // Frontend Dark
            Setting::set('front_dark_brand_primary', '#34d399', 'front_dark_theme');
            Setting::set('front_dark_brand_hover', '#6ee7b7', 'front_dark_theme');
            Setting::set('front_dark_brand_light', '#064e3b', 'front_dark_theme');
            Setting::set('front_dark_accent_color', '#10b981', 'front_dark_theme');
            Setting::set('front_dark_btn_primary_bg', '#059669', 'front_dark_theme');
            Setting::set('front_dark_btn_primary_text', '#ffffff', 'front_dark_theme');
            Setting::set('front_dark_btn_primary_hover', '#10b981', 'front_dark_theme');
            Setting::set('front_dark_body_bg', '#020617', 'front_dark_theme');
            Setting::set('front_dark_card_bg', '#0f172a', 'front_dark_theme');
            Setting::set('front_dark_card_border', '#1e293b', 'front_dark_theme');
            Setting::set('front_dark_header_bg', '#0b1120', 'front_dark_theme');
            Setting::set('front_dark_topbar_bg', '#020617', 'front_dark_theme');
            Setting::set('front_dark_topbar_text', '#94a3b8', 'front_dark_theme');
            Setting::set('front_dark_footer_bg', '#020617', 'front_dark_theme');
            Setting::set('front_dark_footer_text', '#64748b', 'front_dark_theme');
            Setting::set('front_dark_text_primary', '#f1f5f9', 'front_dark_theme');
            Setting::set('front_dark_text_muted', '#94a3b8', 'front_dark_theme');
            Setting::set('front_dark_input_bg', '#0b1324', 'front_dark_theme');
            Setting::set('front_dark_input_border', '#334155', 'front_dark_theme');
            Setting::set('front_dark_dropdown_bg', '#0f172a', 'front_dark_theme');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Colors and theme settings have been reset to default values!');
    }
}
