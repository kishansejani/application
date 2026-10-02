<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If API or Accept-Language header is provided
        if ($request->hasHeader('Accept-Language')) {
            $lang = $request->header('Accept-Language');
            if (in_array(strtolower($lang), ['gu', 'gujarati', 'gu-in'])) {
                App::setLocale('gu');
            } else {
                App::setLocale('en');
            }
        } elseif ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, ['gu', 'en'])) {
                App::setLocale($lang);
                Session::put('locale', $lang);
            }
        } elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } elseif ($request->user() && $request->user()->language) {
            App::setLocale($request->user()->language);
        } else {
            App::setLocale('en');
        }

        return $next($request);
    }
}
