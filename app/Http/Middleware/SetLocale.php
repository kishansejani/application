<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'gu'];

    /**
     * Resolve the UI language.
     *
     * Web:  ?lang=  →  session (set by the language switcher)  →  logged-in user's preference  →  English
     * API:  ?lang=  →  X-Locale / Accept-Language header  →  user's preference  →  English
     *
     * Note: browsers always send an Accept-Language header (e.g. "en-US,en;q=0.9"), so it must NOT
     * override the session on web requests – that was why the Gujarati switch appeared to do nothing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isApi = $request->is('api/*') || $request->expectsJson() && !$request->hasSession();

        $locale = $this->normalize($request->query('lang'));

        if ($locale && !$isApi && $request->hasSession()) {
            Session::put('locale', $locale);
        }

        if (!$locale && $isApi) {
            $locale = $this->normalize($request->header('X-Locale'))
                ?? $this->fromAcceptLanguage($request->header('Accept-Language'));
        }

        if (!$locale && !$isApi && $request->hasSession()) {
            $locale = $this->normalize(Session::get('locale'));
        }

        if (!$locale && $request->user() && $request->user()->language) {
            $locale = $this->normalize($request->user()->language);
            if ($locale && !$isApi && $request->hasSession()) {
                Session::put('locale', $locale);
            }
        }

        App::setLocale($locale ?? config('app.locale', 'en'));

        return $next($request);
    }

    private function normalize($value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        $value = strtolower(trim($value));
        if (in_array($value, ['gujarati', 'gu-in', 'gu_in'], true)) {
            $value = 'gu';
        }
        return in_array($value, self::SUPPORTED, true) ? $value : null;
    }

    /** Only the *first* language in the header counts ("gu-IN,en;q=0.8" → gu). */
    private function fromAcceptLanguage(?string $header): ?string
    {
        if (!$header) {
            return null;
        }
        $first = strtolower(trim(explode(',', $header)[0]));
        $first = explode(';', $first)[0];
        return str_starts_with($first, 'gu') ? 'gu' : ($first !== '' ? 'en' : null);
    }
}
