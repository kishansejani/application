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
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            'theme_primary_color' => '#000000',
            'theme_hover_color' => '#a1a1a1',
            'sidebar_bg_color' => '#000000',
            'sidebar_active_color' => '#add8e6',
            'footer_copyright_prefix' => '© 2026, made with ❤️ by',
            'footer_creator_name' => 'Decent Infoways',
            'footer_creator_url' => 'https://decentinfoways.com',
            'theme_mode' => 'system', // light, dark, system
        ];

        $settings = self::all()->pluck('value', 'key')->toArray();
        return array_merge($defaults, $settings);
    }
}
