@php
    $sysSettings = \App\Models\Setting::getAllSettings();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Portal') - {{ config('app.name', 'Decent Infoways') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Hind+Vadodara:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: 'var(--theme-primary)',
                        hover: 'var(--theme-hover)',
                        sidebarBg: 'var(--sidebar-bg)',
                        sidebarActive: 'var(--sidebar-active)',
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Hind Vadodara"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.tailwindcss.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --theme-primary: {{ $sysSettings['theme_primary_color'] ?? '#0f172a' }};
            --theme-hover: {{ $sysSettings['theme_hover_color'] ?? '#334155' }};
            --btn-primary-bg: {{ $sysSettings['btn_primary_bg'] ?? '#0f172a' }};
            --btn-primary-text: {{ $sysSettings['btn_primary_text'] ?? '#ffffff' }};
            --btn-primary-hover: {{ $sysSettings['btn_primary_hover'] ?? '#1e293b' }};
            --btn-accent-bg: {{ $sysSettings['btn_accent_bg'] ?? '#10b981' }};
            --btn-accent-text: {{ $sysSettings['btn_accent_text'] ?? '#ffffff' }};
            --sidebar-bg: {{ $sysSettings['sidebar_bg_color'] ?? '#000000' }};
            --sidebar-active: {{ $sysSettings['sidebar_active_color'] ?? '#add8e6' }};
            --sidebar-text: {{ $sysSettings['sidebar_text_color'] ?? '#ffffff' }};
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Hind Vadodara', sans-serif;
        }
        .btn-theme-primary, .btn-primary-custom { 
            background-color: var(--btn-primary-bg) !important; 
            color: var(--btn-primary-text) !important; 
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-theme-primary:hover, .btn-primary-custom:hover {
            background-color: var(--btn-primary-hover) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-theme-accent {
            background-color: var(--btn-accent-bg) !important;
            color: var(--btn-accent-text) !important;
            transition: all 0.2s ease;
        }
        .bg-primary-custom { background-color: var(--theme-primary) !important; }
        .text-primary-custom { color: var(--theme-primary) !important; }
        .border-primary-custom { border-color: var(--theme-primary) !important; }
        .sidebar-custom-bg { background-color: var(--sidebar-bg) !important; }
        .sidebar-active-item { 
            background-color: var(--sidebar-active) !important; 
            color: #000000 !important;
            font-weight: 700 !important;
        }
        
        /* Collapsible Mini Sidebar Styles */
        #adminSidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #adminSidebar.collapsed {
            width: 5rem !important; /* 80px */
        }
        #adminSidebar.collapsed .sidebar-text,
        #adminSidebar.collapsed .sidebar-heading,
        #adminSidebar.collapsed .sidebar-badge,
        #adminSidebar.collapsed .sidebar-brand-text,
        #adminSidebar.collapsed .sidebar-chevron {
            display: none !important;
        }
        #adminSidebar.collapsed .sidebar-item {
            justify-content: center !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        #adminSidebar.collapsed .sidebar-brand-wrapper {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Tooltip styling for collapsed sidebar */
        #adminSidebar.collapsed .sidebar-item {
            position: relative;
        }

        /* Modern DataTables Styling */
        .dataTables_wrapper {
            width: 100% !important;
        }
        .dataTables_wrapper .dt-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (min-width: 640px) {
            .dataTables_wrapper .dt-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .dataTables_wrapper .dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
        }
        .dark .dataTables_wrapper .dataTables_length label {
            color: #94a3b8;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            color: #0f172a;
            border-radius: 0.75rem;
            padding: 0.45rem 2rem 0.45rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            outline: none;
            transition: all 0.2s ease;
        }
        .dark .dataTables_wrapper .dataTables_length select {
            border-color: #334155;
            background-color: #0f172a;
            color: #f8fafc;
        }
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #0f172a;
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.1);
        }
        .dataTables_wrapper .dataTables_filter {
            margin: 0;
            position: relative;
        }
        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            position: relative;
            margin: 0;
            font-size: 0;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            color: #0f172a;
            border-radius: 0.875rem;
            padding: 0.55rem 1rem 0.55rem 2.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            width: 16rem;
            outline: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .dark .dataTables_wrapper .dataTables_filter input {
            border-color: #334155;
            background-color: #0f172a;
            color: #f8fafc;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
            width: 18rem;
        }
        .dark .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15);
        }
        .dataTables_wrapper .dataTables_filter::before {
            content: "\f002";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.8125rem;
            pointer-events: none;
            z-index: 10;
        }
        .dataTables_wrapper table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
            width: 100% !important;
        }
        .dataTables_wrapper table.dataTable thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.875rem 1rem;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .dataTables_wrapper table.dataTable thead th {
            background-color: #0f172a;
            color: #94a3b8;
            border-color: #1e293b;
        }
        .dataTables_wrapper table.dataTable tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .dataTables_wrapper table.dataTable tbody td {
            border-bottom: 1px solid #1e293b;
        }
        .dataTables_wrapper .dt-footer {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.25rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
        }
        .dark .dataTables_wrapper .dt-footer {
            border-color: #1e293b;
        }
        @media (min-width: 640px) {
            .dataTables_wrapper .dt-footer {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #64748b;
            padding: 0;
            margin: 0;
        }
        .dark .dataTables_wrapper .dataTables_info {
            color: #94a3b8;
        }
        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0;
            margin: 0;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 2.25rem !important;
            height: 2.25rem !important;
            padding: 0 0.625rem !important;
            border-radius: 0.75rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            border: 1px solid transparent !important;
            background: transparent !important;
            color: #64748b !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #94a3b8 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #e2e8f0 !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background-color: #1e293b !important;
            color: #ffffff !important;
            border-color: #334155 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #ffffff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.35 !important;
            cursor: not-allowed !important;
        }

        .dropzone-container {
            border: 2px dashed #cbd5e1;
            transition: all 0.2s ease-in-out;
        }
        .dropzone-container.dragover {
            border-color: var(--theme-primary);
            background-color: rgba(0, 0, 0, 0.05);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 antialiased flex flex-col transition-colors duration-200">

    <div class="min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-30 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <!-- Left: Sidebar Toggle & Search Bar (Ctrl+/) -->
                <div class="flex items-center gap-3 sm:gap-4 flex-1">
                    <!-- Desktop & Mobile Sidebar Collapse Toggle Button -->
                    <button id="sidebarCollapseToggle" type="button" class="p-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition flex items-center justify-center" title="Toggle Sidebar Width">
                        <i class="fa-solid fa-bars-staggered text-base"></i>
                    </button>
                    
                    <!-- Search Input -->
                    <div class="relative w-full max-w-md hidden sm:block">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input type="text" id="adminQuickSearch" placeholder="Search anything (Ctrl+/)..."
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    </div>
                </div>

                <!-- Right Controls: Theme Mode (Light/Dark/System), Language, Storefront & Profile -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <!-- Theme Mode Dropdown (Light / Dark / System Match PC) -->
                    <div class="relative" id="themeDropdownContainer">
                        <button id="themeModeBtn" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            <i id="themeCurrentIcon" class="fa-solid fa-laptop text-slate-500 dark:text-slate-300"></i>
                            <span id="themeCurrentLabel" class="hidden md:inline text-slate-700 dark:text-slate-200">System</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div id="themeDropdownMenu" class="hidden absolute right-0 mt-2 w-40 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-1 z-50 text-xs font-semibold">
                            <button onclick="setThemeMode('light')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2.5 text-slate-700 dark:text-slate-200">
                                <i class="fa-solid fa-sun text-amber-500 w-4"></i> Light
                            </button>
                            <button onclick="setThemeMode('dark')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2.5 text-slate-700 dark:text-slate-200">
                                <i class="fa-solid fa-moon text-indigo-400 w-4"></i> Dark
                            </button>
                            <button onclick="setThemeMode('system')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2.5 text-slate-700 dark:text-slate-200">
                                <i class="fa-solid fa-laptop text-slate-500 w-4"></i> System (PC)
                            </button>
                        </div>
                    </div>

                    <!-- Language Switcher -->
                    <a href="{{ route('lang.switch', app()->getLocale() === 'gu' ? 'en' : 'gu') }}" 
                       class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <i class="fa-solid fa-language text-slate-500"></i>
                        <span>{{ app()->getLocale() === 'gu' ? 'ENG' : 'ગુજ' }}</span>
                    </a>

                    <!-- Visit Storefront -->
                    <a href="{{ route('home') }}" target="_blank" 
                       class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl transition border border-slate-200 dark:border-slate-600">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Storefront</span>
                    </a>

                    <!-- Admin Profile -->
                    <div class="relative pl-1 sm:pl-2">
                        <div class="flex items-center gap-2 cursor-pointer" id="userMenuBtn">
                            <div class="relative">
                                <div class="w-9 h-9 rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-black flex items-center justify-center font-bold text-sm shadow">
                                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-800"></span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-bold text-slate-900 dark:text-white leading-none">{{ Auth::user()->name ?? 'Administrator' }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5 uppercase">{{ Auth::user()->roleModel->display_name ?? Auth::user()->role ?? 'Admin' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Wrapper with Sidebar & Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar Navigation (Collapsible to Icon-Only Mode) -->
            <aside id="adminSidebar" class="w-64 sidebar-custom-bg text-slate-300 flex-shrink-0 flex flex-col justify-between hidden lg:flex z-20">
                <div class="p-3 space-y-1 overflow-y-auto max-h-[calc(100vh-4rem)]">
                    <!-- Brand Header in Sidebar -->
                    <div class="sidebar-brand-wrapper flex items-center justify-between px-3 py-3 mb-2 border-b border-white/10">
                        <div class="flex items-center gap-2.5 overflow-hidden">
                            <div class="w-9 h-9 flex-shrink-0 rounded-xl bg-white/10 flex items-center justify-center text-white font-black text-base shadow-sm">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div class="sidebar-brand-text truncate">
                                <span class="font-extrabold text-sm tracking-tight text-white block truncate">{{ $sysSettings['footer_creator_name'] ?? 'Decent Infoways' }}</span>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Admin Panel</span>
                            </div>
                        </div>
                    </div>

                    <p class="sidebar-heading px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-3 mb-1">Core</p>

                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Dashboard">
                        <i class="fa-solid fa-chart-pie text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>

                    <!-- Orders -->
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-item flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.orders.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Orders">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-truck-fast text-sm w-5 text-center flex-shrink-0"></i>
                            <span class="sidebar-text">Orders</span>
                        </div>
                        @php $pendingCnt = \App\Models\Order::where('order_status', 'pending')->count(); @endphp
                        @if($pendingCnt > 0)
                            <span class="sidebar-badge px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-400 text-black">{{ $pendingCnt }}</span>
                        @endif
                    </a>

                    <p class="sidebar-heading px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1">Catalog & Stock</p>

                    <!-- Sliders (Clean Name as requested) -->
                    <a href="{{ route('admin.sliders.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.sliders.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Sliders">
                        <i class="fa-solid fa-images text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Sliders</span>
                    </a>

                    <!-- Categories -->
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.categories.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Categories">
                        <i class="fa-solid fa-layer-group text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Categories</span>
                    </a>

                    <!-- Sub Categories -->
                    <a href="{{ route('admin.subcategories.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.subcategories.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Sub Categories">
                        <i class="fa-solid fa-sitemap text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Sub Categories</span>
                    </a>

                    <!-- Products -->
                    <a href="{{ route('admin.products.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.products.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Products">
                        <i class="fa-solid fa-boxes-stacked text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Products</span>
                    </a>

                    <!-- Stock -->
                    <a href="{{ route('admin.stock.index') }}" class="sidebar-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.stock.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Stock & Inventory">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-warehouse text-sm w-5 text-center flex-shrink-0"></i>
                            <span class="sidebar-text">Stock</span>
                        </div>
                        @php $lowStockCount = \App\Models\Product::where('stock_quantity', '<=', 5)->count(); @endphp
                        @if($lowStockCount > 0)
                            <span class="sidebar-badge px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-500 text-white">Low</span>
                        @endif
                    </a>

                    <!-- Offers & Pages -->
                    <p class="sidebar-heading px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1">Marketing & Content</p>

                    <a href="{{ route('admin.offers.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.offers.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Offers">
                        <i class="fa-solid fa-tag text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Offers</span>
                    </a>

                    <a href="{{ route('admin.pages.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.pages.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Pages">
                        <i class="fa-solid fa-file-contract text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Pages</span>
                    </a>

                    <!-- Role & User Management -->
                    <p class="sidebar-heading px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1">Administration</p>

                    <a href="{{ route('admin.roles.index') }}" class="sidebar-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.roles.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Roles & Permissions">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-shield-halved text-sm w-5 text-center flex-shrink-0"></i>
                            <span class="sidebar-text">Roles & Permissions</span>
                        </div>
                        <i class="sidebar-chevron fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Users">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users-gear text-sm w-5 text-center flex-shrink-0"></i>
                            <span class="sidebar-text">Users</span>
                        </div>
                        <i class="sidebar-chevron fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </a>

                    <!-- Settings -->
                    <p class="sidebar-heading px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-3 pb-1">Settings</p>

                    <a href="{{ route('admin.settings.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.settings.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}" title="Settings">
                        <i class="fa-solid fa-gear text-sm w-5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">Settings</span>
                    </a>
                </div>

                <!-- Sidebar Footer & Logout -->
                <div class="p-3 border-t border-white/10 bg-black/40">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-item w-full flex items-center justify-center gap-2 py-2.5 px-3 bg-white/10 hover:bg-rose-600/40 text-rose-300 hover:text-rose-100 rounded-xl text-xs font-bold transition" title="Logout Account">
                            <i class="fa-solid fa-arrow-right-from-bracket flex-shrink-0"></i>
                            <span class="sidebar-text">Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content Container -->
            <main class="flex-1 overflow-y-auto bg-slate-100 dark:bg-slate-900 p-4 sm:p-6 lg:p-8 flex flex-col justify-between">
                <div>
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm animate-fade-in">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                                <span class="font-semibold text-sm">{{ session('success') }}</span>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm animate-fade-in">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                                <span class="font-semibold text-sm">{{ session('error') }}</span>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    @endif

                    @yield('content')
                </div>

                <!-- Footer (Matching Screenshot Footer: © 2026, made with ❤️ by Decent Infoways) -->
                <footer class="mt-12 pt-6 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <div>
                        <span>{{ $sysSettings['footer_copyright_prefix'] ?? '© 2026, made with ❤️ by' }}</span>
                        <a href="{{ $sysSettings['footer_creator_url'] ?? 'https://decentinfoways.com' }}" target="_blank" class="font-bold hover:underline text-slate-800 dark:text-white">
                            {{ $sysSettings['footer_creator_name'] ?? 'Decent Infoways' }}
                        </a>
                    </div>
                    <div class="flex items-center gap-4 text-[11px]">
                        <a href="{{ route('admin.settings.index') }}" class="hover:underline">Settings</a>
                        <span>•</span>
                        <a href="{{ route('password.forgot') }}" class="hover:underline">Reset Password</a>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Setup CSRF Token
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // 3-Option Theme Mode Switcher (Light / Dark / System Match PC)
        function applyTheme(mode) {
            const html = document.documentElement;
            let isDark = false;

            if (mode === 'dark') {
                isDark = true;
            } else if (mode === 'light') {
                isDark = false;
            } else {
                // System (Match PC Windows / Mac / Linux OS setting)
                isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            if (isDark) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }

            // Update icon & label
            const icon = document.getElementById('themeCurrentIcon');
            const label = document.getElementById('themeCurrentLabel');
            if (icon && label) {
                if (mode === 'light') {
                    icon.className = 'fa-solid fa-sun text-amber-500';
                    label.textContent = 'Light';
                } else if (mode === 'dark') {
                    icon.className = 'fa-solid fa-moon text-indigo-400';
                    label.textContent = 'Dark';
                } else {
                    icon.className = 'fa-solid fa-laptop text-slate-500 dark:text-slate-300';
                    label.textContent = 'System';
                }
            }
        }

        function setThemeMode(mode) {
            localStorage.setItem('admin_theme_mode', mode);
            applyTheme(mode);
            $('#themeDropdownMenu').addClass('hidden');
        }

        // Initialize Theme Mode on page load
        const savedThemeMode = localStorage.getItem('admin_theme_mode') || '{{ $sysSettings["theme_mode"] ?? "system" }}';
        applyTheme(savedThemeMode);

        // Listen for OS system theme changes if in 'system' mode
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                const current = localStorage.getItem('admin_theme_mode') || 'system';
                if (current === 'system') {
                    applyTheme('system');
                }
            });
        }

        // Theme dropdown toggle
        $('#themeModeBtn').on('click', function(e) {
            e.stopPropagation();
            $('#themeDropdownMenu').toggleClass('hidden');
        });

        // Sidebar Collapsible Mini/Icon-Only Toggle & Mobile Responsive Toggle
        function applySidebarState(isCollapsed) {
            const sidebar = $('#adminSidebar');
            if (isCollapsed) {
                sidebar.addClass('collapsed');
            } else {
                sidebar.removeClass('collapsed');
            }
        }

        // Initialize sidebar state from localStorage
        const savedSidebarState = localStorage.getItem('admin_sidebar_collapsed') === 'true';
        if (savedSidebarState && window.innerWidth >= 1024) {
            $('#adminSidebar').addClass('collapsed');
        }

        $('#sidebarCollapseToggle').on('click', function() {
            const sidebar = $('#adminSidebar');
            if (window.innerWidth < 1024) {
                // Mobile: toggle visibility
                sidebar.toggleClass('hidden');
            } else {
                // Desktop: toggle mini icon-only collapsed mode
                sidebar.toggleClass('collapsed');
                const isCollapsed = sidebar.hasClass('collapsed');
                localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            }
        });

        // Setup DataTables Global Defaults with modern design
        if ($.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                responsive: true,
                language: {
                    search: "",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: '<i class="fa-solid fa-angles-left"></i>',
                        previous: '<i class="fa-solid fa-chevron-left"></i>',
                        next: '<i class="fa-solid fa-chevron-right"></i>',
                        last: '<i class="fa-solid fa-angles-right"></i>'
                    }
                },
                dom: '<"dt-header flex flex-col sm:flex-row items-center justify-between gap-4 mb-4"lf>rt<"dt-footer flex flex-col sm:flex-row items-center justify-between gap-4 mt-4"ip>',
                drawCallback: function() {
                    // Modernize pagination buttons styling on render
                    $('.dataTables_paginate .paginate_button').addClass('transition duration-150');
                }
            });
        }

        // Quick Search keyboard shortcut Ctrl+/
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                const search = document.getElementById('adminQuickSearch');
                if (search) search.focus();
            }
        });

        // Universal Delete Confirmation with SweetAlert2
        $(document).on('click', '.confirm-delete-btn', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Drag & Drop Uploader Helper
        function initDragAndDropUploader(dropAreaId, inputId, previewId) {
            const dropArea = document.getElementById(dropAreaId);
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            if (!dropArea || !input) return;

            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropArea.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropArea.classList.remove('dragover');
                }, false);
            });

            dropArea.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length) {
                    input.files = files;
                    showPreview(files);
                }
            });

            input.addEventListener('change', () => {
                if (input.files.length) {
                    showPreview(input.files);
                }
            });

            function showPreview(files) {
                if (!preview) return;
                preview.innerHTML = '';
                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const imgContainer = document.createElement('div');
                            imgContainer.className = 'relative group w-24 h-24 rounded-xl overflow-hidden border border-slate-200 shadow-sm';
                            imgContainer.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover"><div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold">Selected</div>`;
                            preview.appendChild(imgContainer);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
