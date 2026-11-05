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

    /** Choice lists for the select-type design settings (also used for validation). */
    public const DESIGN_CHOICES = [
        'ui_style'  => ['aurora', 'soft', 'glass', 'bold', 'minimal'],
        'ui_radius' => ['sharp', 'soft', 'round', 'pill'],
        'ui_font'   => ['jakarta', 'poppins', 'outfit', 'manrope', 'sora'],
    ];

    /** On/off design settings stored as '1' / '0'. */
    public const DESIGN_TOGGLES = [
        'fx_aurora', 'fx_reveal', 'fx_tilt', 'fx_glow', 'fx_marquee', 'fx_glass_header', 'fx_float_tabbar', 'fx_progress',
        'fx_scene3d', 'fx_product3d',
        'spin_autoplay', 'spin_wheel', 'spin_hint', 'spin_hover_scrub',
    ];

    /** Numeric (1-10) design settings. */
    public const DESIGN_SCALES = ['spin_speed', 'spin_sensitivity'];

    /** Defaults for the "Design & Experience" settings (website + mobile app). */
    public static function designDefaults(): array
    {
        return [
            'ui_style' => 'aurora',
            'ui_radius' => 'round',
            'ui_font' => 'jakarta',

            'fx_aurora' => '1',
            'fx_reveal' => '1',
            'fx_tilt' => '1',
            'fx_glow' => '1',
            'fx_marquee' => '1',
            'fx_glass_header' => '1',
            'fx_float_tabbar' => '1',
            'fx_progress' => '1',
            'fx_scene3d' => '0',
            'fx_product3d' => '0',

            'spin_autoplay' => '1',
            'spin_speed' => '5',
            'spin_sensitivity' => '5',
            'spin_wheel' => '1',
            'spin_hint' => '1',
            'spin_hover_scrub' => '1',
        ];
    }

    /** Settings the mobile app needs (palette + design). Light and dark are both returned. */
    public static function appTheme(): array
    {
        $s = self::getAllSettings();
        $pick = fn (array $map) => collect($map)->map(fn ($key) => $s[$key] ?? null)->all();

        return [
            'light' => $pick([
                'primary' => 'front_brand_primary', 'primary_hover' => 'front_brand_hover', 'primary_soft' => 'front_brand_light',
                'accent' => 'front_accent_color', 'button_bg' => 'front_btn_primary_bg', 'button_text' => 'front_btn_primary_text',
                'background' => 'front_body_bg', 'surface' => 'front_card_bg', 'border' => 'front_card_border',
                'header' => 'front_header_bg', 'text' => 'front_text_primary', 'text_muted' => 'front_text_muted',
                'input_bg' => 'front_input_bg', 'input_border' => 'front_input_border',
            ]),
            'dark' => $pick([
                'primary' => 'front_dark_brand_primary', 'primary_hover' => 'front_dark_brand_hover', 'primary_soft' => 'front_dark_brand_light',
                'accent' => 'front_dark_accent_color', 'button_bg' => 'front_dark_btn_primary_bg', 'button_text' => 'front_dark_btn_primary_text',
                'background' => 'front_dark_body_bg', 'surface' => 'front_dark_card_bg', 'border' => 'front_dark_card_border',
                'header' => 'front_dark_header_bg', 'text' => 'front_dark_text_primary', 'text_muted' => 'front_dark_text_muted',
                'input_bg' => 'front_dark_input_bg', 'input_border' => 'front_dark_input_border',
            ]),
            'design' => [
                'style' => $s['ui_style'],
                'radius' => $s['ui_radius'],
                'font' => $s['ui_font'],
                'effects' => [
                    'aurora' => $s['fx_aurora'] === '1', 'reveal' => $s['fx_reveal'] === '1',
                    'tilt' => $s['fx_tilt'] === '1', 'glow' => $s['fx_glow'] === '1',
                    'glass_header' => $s['fx_glass_header'] === '1', 'float_tabbar' => $s['fx_float_tabbar'] === '1',
                ],
                'spin' => [
                    'autoplay' => $s['spin_autoplay'] === '1',
                    'speed' => (int) $s['spin_speed'],
                    'sensitivity' => (int) $s['spin_sensitivity'],
                    'hint' => $s['spin_hint'] === '1',
                ],
            ],
            'store_name' => $s['store_name'],
            'theme_mode' => $s['theme_mode'],
            'version' => md5(json_encode(array_intersect_key($s, array_flip(array_merge(
                ['front_brand_primary', 'front_dark_brand_primary', 'front_body_bg', 'front_dark_body_bg'],
                array_keys(self::designDefaults())
            ))))),
        ];
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            // Admin Panel colors
            'theme_primary_color' => '#0f172a',
            'theme_hover_color' => '#334155',
            'admin_icon_color' => '#0f172a',
            'admin_icon_hover_color' => '#334155',
            'admin_action_view_color' => '#4f46e5',
            'admin_action_view_hover' => '#3730a3',
            'admin_action_edit_color' => '#059669',
            'admin_action_edit_hover' => '#047857',
            'admin_action_delete_color' => '#e11d48',
            'admin_action_delete_hover' => '#be123c',
            'admin_action_redirect_color' => '#0284c7',
            'admin_action_redirect_hover' => '#0369a1',
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
        return array_merge($defaults, self::designDefaults(), $settings);
    }
}
