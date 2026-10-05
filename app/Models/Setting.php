<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value, string $group = 'general')
    {
        Cache::forget("setting_{$key}");
        Cache::forget("all_settings");
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            // Admin Panel colors
            'theme_primary_color' => '#0f172a',
            'theme_hover_color' => '#334155',
            'btn_primary_bg' => '#0f172a',
            'btn_primary_text' => '#ffffff',
            'btn_primary_hover' => '#1e293b',
            'btn_accent_bg' => '#10b981',
            'btn_accent_text' => '#ffffff',
            'sidebar_bg_color' => '#000000',
            'sidebar_active_color' => '#add8e6',
            'sidebar_text_color' => '#ffffff',
            'footer_copyright_prefix' => '© 2026, made with ❤️ by',
            'footer_creator_name' => 'Decent Infoways',
            'footer_creator_url' => 'https://decentinfoways.com',
            'theme_mode' => 'system', // light, dark, system
            'store_name' => 'Fresh Express',

            // Frontend Web Colors — Light Mode
            'front_brand_primary' => '#059669',
            'front_brand_hover' => '#047857',
            'front_brand_light' => '#ecfdf5',
            'front_accent_color' => '#10b981',
            'front_btn_primary_bg' => '#059669',
            'front_btn_primary_text' => '#ffffff',
            'front_btn_primary_hover' => '#047857',
            'front_btn_accent_bg' => '#10b981',
            'front_btn_accent_text' => '#ffffff',
            'front_topbar_bg' => '#064e3b',
            'front_topbar_text' => '#ecfdf5',
            'front_header_bg' => '#ffffff',
            'front_body_bg' => '#f6f8f7',
            'front_card_bg' => '#ffffff',
            'front_card_border' => '#e2e8f0',
            'front_footer_bg' => '#0f172a',
            'front_footer_text' => '#94a3b8',
            'front_text_primary' => '#0f172a',
            'front_text_muted' => '#64748b',
            'front_input_bg' => '#ffffff',
            'front_input_border' => '#cbd5e1',
            'front_dropdown_bg' => '#ffffff',

            // Frontend Web Colors — Dark Mode
            'front_dark_brand_primary' => '#34d399',
            'front_dark_brand_hover' => '#6ee7b7',
            'front_dark_brand_light' => '#064e3b',
            'front_dark_accent_color' => '#10b981',
            'front_dark_btn_primary_bg' => '#059669',
            'front_dark_btn_primary_text' => '#ffffff',
            'front_dark_btn_primary_hover' => '#10b981',
            'front_dark_body_bg' => '#020617',
            'front_dark_card_bg' => '#0f172a',
            'front_dark_card_border' => '#1e293b',
            'front_dark_header_bg' => '#0b1120',
            'front_dark_topbar_bg' => '#020617',
            'front_dark_topbar_text' => '#94a3b8',
            'front_dark_footer_bg' => '#020617',
            'front_dark_footer_text' => '#64748b',
            'front_dark_text_primary' => '#f1f5f9',
            'front_dark_text_muted' => '#94a3b8',
            'front_dark_input_bg' => '#0b1324',
            'front_dark_input_border' => '#334155',
            'front_dark_dropdown_bg' => '#0f172a',
        ];

        $settings = self::all()->pluck('value', 'key')->toArray();
        return array_merge($defaults, $settings);
    }
}
