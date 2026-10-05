<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - FreshExpress</title>

    {{-- Theme before first paint: follows the admin panel preference, then the storefront one, then the OS --}}
    <script>
        (function () {
            var m = 'system';
            try { m = localStorage.getItem('admin_theme_mode') || localStorage.getItem('theme') || 'system'; } catch (e) {}
            if (m === 'dark' || (m === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Vadodara:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/duotone/style.css">
    @php
        $sysSettings = \App\Models\Setting::getAllSettings();
        $fBrandPri   = $sysSettings['front_brand_primary'] ?? '#059669';
        $fBrandHov   = $sysSettings['front_brand_hover'] ?? '#047857';
        $fBrandLt    = $sysSettings['front_brand_light'] ?? '#ecfdf5';
        $fAccent     = $sysSettings['front_accent_color'] ?? '#10b981';
        $fBtnPriBg   = $sysSettings['front_btn_primary_bg'] ?? $fBrandPri;
        $fBtnPriTxt  = $sysSettings['front_btn_primary_text'] ?? '#ffffff';
        $fBtnPriHov  = $sysSettings['front_btn_primary_hover'] ?? $fBrandHov;

        $fdBrandPri  = $sysSettings['front_dark_brand_primary'] ?? '#34d399';
        $fdBrandHov  = $sysSettings['front_dark_brand_hover'] ?? '#6ee7b7';
        $fdBrandLt   = $sysSettings['front_dark_brand_light'] ?? '#064e3b';
        $fdAccent    = $sysSettings['front_dark_accent_color'] ?? '#10b981';
        $fdBtnPriBg  = $sysSettings['front_dark_btn_primary_bg'] ?? '#059669';
        $fdBtnPriTxt = $sysSettings['front_dark_btn_primary_text'] ?? '#ffffff';
        $fdBtnPriHov = $sysSettings['front_dark_btn_primary_hover'] ?? '#10b981';
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/front/store.css') }}?v=5">
    <style>
        :root {
            --fx-brand-primary: {{ $fBrandPri }};
            --fx-brand-hover: {{ $fBrandHov }};
            --fx-brand-light: {{ $fBrandLt }};
            --fx-accent-color: {{ $fAccent }};
            --fx-btn-primary-bg: {{ $fBtnPriBg }};
            --fx-btn-primary-hover: {{ $fBtnPriHov }};
            --fx-btn-primary-text: {{ $fBtnPriTxt }};
        }
        html.dark {
            --fx-brand-primary: {{ $fdBrandPri }};
            --fx-brand-hover: {{ $fdBrandHov }};
            --fx-brand-light: {{ $fdBrandLt }};
            --fx-accent-color: {{ $fdAccent }};
            --fx-btn-primary-bg: {{ $fdBtnPriBg }};
            --fx-btn-primary-hover: {{ $fdBtnPriHov }};
            --fx-btn-primary-text: {{ $fdBtnPriTxt }};
        }
    </style>
    @stack('styles')

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '{{ $fBrandLt }}',
                            100: 'color-mix(in srgb, {{ $fBrandPri }} 15%, #ffffff)',
                            200: 'color-mix(in srgb, {{ $fBrandPri }} 30%, #ffffff)',
                            300: 'color-mix(in srgb, {{ $fBrandPri }} 50%, #ffffff)',
                            400: 'color-mix(in srgb, {{ $fBrandPri }} 75%, #ffffff)',
                            500: '{{ $fAccent }}',
                            600: '{{ $fBrandPri }}',
                            700: '{{ $fBrandHov }}',
                            800: 'color-mix(in srgb, {{ $fBrandPri }} 80%, #000000)',
                            900: 'color-mix(in srgb, {{ $fBrandPri }} 60%, #000000)',
                            950: 'color-mix(in srgb, {{ $fBrandPri }} 40%, #000000)',
                        },
                        primary: {
                            50: '{{ $fBrandLt }}',
                            100: 'color-mix(in srgb, {{ $fBrandPri }} 15%, #ffffff)',
                            200: 'color-mix(in srgb, {{ $fBrandPri }} 30%, #ffffff)',
                            300: 'color-mix(in srgb, {{ $fBrandPri }} 50%, #ffffff)',
                            400: 'color-mix(in srgb, {{ $fBrandPri }} 75%, #ffffff)',
                            500: '{{ $fAccent }}',
                            600: '{{ $fBrandPri }}',
                            700: '{{ $fBrandHov }}',
                            800: 'color-mix(in srgb, {{ $fBrandPri }} 80%, #000000)',
                            900: 'color-mix(in srgb, {{ $fBrandPri }} 60%, #000000)',
                            950: 'color-mix(in srgb, {{ $fBrandPri }} 40%, #000000)',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Hind Vadodara"', 'system-ui', 'sans-serif']
                    },
                    screens: { xs: '400px' }
                }
            }
        }
    </script>
</head>
<body class="fx-body h-full antialiased">
<div class="min-h-full grid lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">

    <!-- Brand / info panel -->
    <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden p-10 xl:p-14 text-white bg-[radial-gradient(ellipse_at_top_left,_#065f46_0%,_#022c22_45%,_#020617_100%)]">
        <div class="absolute inset-0 opacity-[.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>
        <div class="absolute -right-24 -bottom-24 w-[28rem] h-[28rem] rounded-full bg-brand-500/20 blur-3xl"></div>

        <a href="{{ route('home') }}" class="relative inline-flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-2xl shadow-lg shadow-brand-500/30"><i class="ph-fill ph-basket"></i></span>
            <span class="leading-tight">
                <span class="block font-extrabold text-lg tracking-tight">FreshExpress</span>
                <span class="block text-[11px] font-bold uppercase tracking-[.16em] text-brand-300">@yield('panel_kicker', 'Admin console')</span>
            </span>
        </a>

        <div class="relative max-w-md">
            @yield('panel')
        </div>

        <p class="relative text-[12px] text-slate-400">&copy; {{ date('Y') }} FreshExpress Grocery · Ahmedabad</p>
    </aside>

    <!-- Form side -->
    <main class="relative flex flex-col min-h-full px-4 sm:px-8 py-6 sm:py-10 bg-white dark:bg-slate-950">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="lg:hidden inline-flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xl"><i class="ph-fill ph-basket"></i></span>
                <span class="font-extrabold text-slate-900 dark:text-white">Fresh<span class="text-brand-600 dark:text-brand-400">Express</span></span>
            </a>
            <div class="ml-auto flex items-center gap-1">
                <button type="button" id="authThemeBtn" class="fx-icon-btn" aria-label="{{ __('auth_ui.toggle_theme') }}" title="{{ __('auth_ui.toggle_theme') }}"><i class="ph ph-moon-stars text-lg"></i></button>
                <a href="{{ route('home') }}" class="fx-btn fx-btn-ghost fx-btn-sm"><i class="ph-bold ph-storefront"></i><span class="hidden xs:inline">{{ __('auth_ui.storefront') }}</span></a>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center py-8">
            <div class="w-full max-w-[26rem]">
                @yield('content')
            </div>
        </div>
    </main>
</div>

<script>
    // password show / hide
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-toggle-password]');
        if (!b) return;
        var input = document.getElementById(b.getAttribute('data-toggle-password'));
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        b.querySelector('i').className = 'ph ' + (show ? 'ph-eye-slash' : 'ph-eye') + ' text-lg';
        b.setAttribute('aria-pressed', show ? 'true' : 'false');
        b.setAttribute('aria-label', show ? @json(__('auth_ui.hide_password')) : @json(__('auth_ui.show_password')));
    });
    // spinner on submit
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return; // handled by an AJAX form (e.g. the OTP screen)
        var btn = e.target.querySelector('button[type=submit]');
        if (btn) setTimeout(function () { btn.classList.add('is-loading'); }, 0);
    });
    // theme toggle (light <-> dark), saved for the admin panel too
    (function () {
        var btn = document.getElementById('authThemeBtn');
        function sync() { btn.querySelector('i').className = 'ph ' + (document.documentElement.classList.contains('dark') ? 'ph-sun' : 'ph-moon-stars') + ' text-lg'; }
        btn.addEventListener('click', function () {
            var dark = !document.documentElement.classList.contains('dark');
            document.documentElement.classList.toggle('dark', dark);
            try { localStorage.setItem('admin_theme_mode', dark ? 'dark' : 'light'); } catch (e) {}
            sync();
        });
        sync();
    })();
</script>
@stack('scripts')
</body>
</html>
