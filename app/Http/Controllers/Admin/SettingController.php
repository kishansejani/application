<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllSettings();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'theme_primary_color' => 'required|string|max:20',
            'theme_hover_color' => 'required|string|max:20',
            'sidebar_bg_color' => 'required|string|max:20',
            'sidebar_active_color' => 'required|string|max:20',
            'footer_copyright_prefix' => 'nullable|string|max:100',
            'footer_creator_name' => 'nullable|string|max:100',
            'footer_creator_url' => 'nullable|url|max:255',
            'theme_mode' => 'nullable|in:light,dark,system',
        ]);

        Setting::set('theme_primary_color', $request->theme_primary_color, 'theme');
        Setting::set('theme_hover_color', $request->theme_hover_color, 'theme');
        Setting::set('sidebar_bg_color', $request->sidebar_bg_color, 'theme');
        Setting::set('sidebar_active_color', $request->sidebar_active_color, 'theme');
        Setting::set('footer_copyright_prefix', $request->footer_copyright_prefix ?? '© 2026, made with ❤️ by', 'footer');
        Setting::set('footer_creator_name', $request->footer_creator_name ?? 'Decent Infoways', 'footer');
        Setting::set('footer_creator_url', $request->footer_creator_url ?? 'https://decentinfoways.com', 'footer');
        
        if ($request->filled('theme_mode')) {
            Setting::set('theme_mode', $request->theme_mode, 'theme');
        }

        return redirect()->route('admin.settings.index')->with('success', 'System and Theme settings updated successfully!');
    }

    public function reset()
    {
        Setting::set('theme_primary_color', '#000000', 'theme');
        Setting::set('theme_hover_color', '#a1a1a1', 'theme');
        Setting::set('sidebar_bg_color', '#000000', 'theme');
        Setting::set('sidebar_active_color', '#add8e6', 'theme');
        Setting::set('footer_copyright_prefix', '© 2026, made with ❤️ by', 'footer');
        Setting::set('footer_creator_name', 'Decent Infoways', 'footer');
        Setting::set('footer_creator_url', 'https://decentinfoways.com', 'footer');
        Setting::set('theme_mode', 'system', 'theme');

        return redirect()->route('admin.settings.index')->with('success', 'Settings have been reset to default values!');
    }
}
