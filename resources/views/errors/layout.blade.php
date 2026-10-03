<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · @yield('title')</title>
    <script>
        (function () { var m = 'system'; try { m = localStorage.getItem('admin_theme_mode') || localStorage.getItem('theme') || 'system'; } catch (e) {}
          if (m === 'dark' || (m === 'system' && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark'); })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/duotone/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] } } } }</script>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="mx-auto w-20 h-20 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm grid place-items-center text-4xl text-slate-700 dark:text-slate-200">
            <i class="ph-duotone ph-@yield('icon')"></i>
        </div>
        <p class="mt-6 text-sm font-bold tracking-widest text-slate-400">ERROR @yield('code')</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight">@yield('title')</h1>
        <p class="mt-3 text-slate-500 dark:text-slate-400">@yield('message')</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 h-11 px-5 rounded-xl border border-slate-300 dark:border-slate-700 font-bold text-sm hover:bg-white dark:hover:bg-slate-900"><i class="ph ph-arrow-left"></i> Go back</a>
            @if(request()->is('admin*'))
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-sm"><i class="ph ph-squares-four"></i> Dashboard</a>
            @else
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700"><i class="ph ph-house"></i> Home</a>
            @endif
        </div>
    </div>
</body>
</html>
