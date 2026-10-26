<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

/**
 * Theme for the mobile app. Everything the admin changes in
 * Settings -> Frontend colours / Dark colours / Design & Experience is returned here,
 * so the Flutter app follows the website without a new release.
 */
class ThemeController extends Controller
{
    public function show()
    {
        return response()->json([
            'status' => true,
            'data' => Setting::appTheme(),
        ])->header('Cache-Control', 'no-cache');
    }
}
