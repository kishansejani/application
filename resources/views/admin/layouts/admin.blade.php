@php
    use App\Models\Order;
    use App\Models\Product;

    $sysSettings = \App\Models\Setting::getAllSettings();
    $authUser    = Auth::user();
    $storeName   = $sysSettings['store_name'] ?? 'Fresh Express';

    // ---- Colour helpers (settings are user-defined hex values) --------------------------
    $hexToRgb = function ($hex, $fallback = '15 23 42') {
        $hex = ltrim(trim((string) $hex), '#');
        if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) { return $fallback; }
        return hexdec(substr($hex, 0, 2)).' '.hexdec(substr($hex, 2, 2)).' '.hexdec(substr($hex, 4, 2));
    };
    $contrast = function ($hex) use ($hexToRgb) {
        [$r, $g, $b] = array_map('intval', explode(' ', $hexToRgb($hex)));
        return ((0.299 * $r + 0.587 * $g + 0.114 * $b) / 255) > 0.6 ? '#0f172a' : '#ffffff';
    };

    $primary       = $sysSettings['theme_primary_color'] ?? '#0f172a';
    $sidebarBg     = $sysSettings['sidebar_bg_color'] ?? '#0b1120';
    $sidebarActive = $sysSettings['sidebar_active_color'] ?? '#add8e6';
    $sidebarText   = $sysSettings['sidebar_text_color'] ?? $contrast($sidebarBg);

    // ---- Live counters for badges & notifications ---------------------------------------
    $pendingCnt  = Order::where('order_status', 'pending')->count();
    $lowStockCnt = Product::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
    $outStockCnt = Product::where('stock_quantity', '<=', 0)->count();
    $notifOrders = Order::where('order_status', 'pending')->latest()->take(5)->get();
    $notifStock  = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('stock_quantity')->take(5)->get();
    $notifTotal  = $pendingCnt + $lowStockCnt + $outStockCnt;

    $can = fn ($perm) => $authUser && ($perm === null || $authUser->hasPermission($perm));

    // ---- Sidebar navigation definition ---------------------------------------------------
    $navGroups = [
        ['label' => 'Overview', 'items' => [
            ['label' => 'Dashboard', 'icon' => 'squares-four', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'perm' => null],
        ]],
        ['label' => 'Sales', 'items' => [
            ['label' => 'Orders', 'icon' => 'shopping-cart-simple', 'route' => 'admin.orders.index', 'active' => ['admin.orders.*', 'admin.invoices.*'], 'perm' => 'manage_orders',
             'badge' => $pendingCnt ?: null, 'badgeTone' => 'amber', 'badgeTitle' => 'Pending orders'],
            ['label' => 'Offers & Coupons', 'icon' => 'ticket', 'route' => 'admin.offers.index', 'active' => 'admin.offers.*', 'perm' => 'manage_offers'],
        ]],
        ['label' => 'Catalog', 'items' => [
            ['label' => 'Categories', 'icon' => 'stack', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'perm' => 'manage_categories'],
            ['label' => 'Sub Categories', 'icon' => 'tree-structure', 'route' => 'admin.subcategories.index', 'active' => 'admin.subcategories.*', 'perm' => 'manage_categories'],
            ['label' => 'Products', 'icon' => 'package', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'perm' => 'manage_products'],
            ['label' => 'Stock & Inventory', 'icon' => 'warehouse', 'route' => 'admin.stock.index', 'active' => 'admin.stock.*', 'perm' => 'manage_stock',
             'badge' => ($lowStockCnt + $outStockCnt) ?: null, 'badgeTone' => 'rose', 'badgeTitle' => 'Low / out of stock'],
        ]],
        ['label' => 'Storefront', 'items' => [
            ['label' => 'Home Sliders', 'icon' => 'slideshow', 'route' => 'admin.sliders.index', 'active' => 'admin.sliders.*', 'perm' => 'manage_sliders'],
            ['label' => 'Content Pages', 'icon' => 'article', 'route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'perm' => 'manage_pages'],
        ]],
        ['label' => 'Administration', 'items' => [
            ['label' => 'Users', 'icon' => 'users-three', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'perm' => 'manage_users'],
            ['label' => 'Roles & Permissions', 'icon' => 'shield-check', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'perm' => 'manage_roles'],
            ['label' => 'Settings', 'icon' => 'gear-six', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*', 'perm' => 'manage_settings'],
        ]],
    ];

    // Filter by permission and work out the active item (for breadcrumbs)
    $activeGroup = null; $activeItem = null;
    foreach ($navGroups as $gi => $group) {
        $navGroups[$gi]['key']   = \Illuminate\Support\Str::slug($group['label']);
        $navGroups[$gi]['label'] = __($group['label']);
        $group['items'] = array_map(fn ($i) => array_merge($i, ['label' => __($i['label'])]), $group['items']);
        $navGroups[$gi]['items'] = array_values(array_filter($group['items'], fn ($i) => $can($i['perm'])));
        foreach ($navGroups[$gi]['items'] as $ii => $item) {
            $isActive = request()->routeIs(...(array) $item['active']);
            $navGroups[$gi]['items'][$ii]['isActive'] = $isActive;
            if ($isActive) { $activeGroup = $group['label']; $activeItem = $item; }
        }
    }
    $navGroups = array_values(array_filter($navGroups, fn ($g) => count($g['items'])));

    // Quick "create" actions for the command palette
    $quickActions = array_values(array_filter([
        $can('manage_products')   ? ['label' => 'Add new product', 'icon' => 'plus-circle', 'url' => route('admin.products.create'), 'hint' => 'Catalog'] : null,
        $can('manage_categories') ? ['label' => 'Add new category', 'icon' => 'plus-circle', 'url' => route('admin.categories.create'), 'hint' => 'Catalog'] : null,
        $can('manage_categories') ? ['label' => 'Add new sub category', 'icon' => 'plus-circle', 'url' => route('admin.subcategories.create'), 'hint' => 'Catalog'] : null,
        $can('manage_offers')     ? ['label' => 'Create coupon / offer', 'icon' => 'plus-circle', 'url' => route('admin.offers.create'), 'hint' => 'Sales'] : null,
        $can('manage_sliders')    ? ['label' => 'Add home slider', 'icon' => 'plus-circle', 'url' => route('admin.sliders.create'), 'hint' => 'Storefront'] : null,
        $can('manage_users')      ? ['label' => 'Add new user', 'icon' => 'user-plus', 'url' => route('admin.users.create'), 'hint' => 'Administration'] : null,
        $can('manage_roles')      ? ['label' => 'Create role', 'icon' => 'shield-plus', 'url' => route('admin.roles.create'), 'hint' => 'Administration'] : null,
        $can('manage_orders')     ? ['label' => 'Pending orders', 'icon' => 'hourglass-medium', 'url' => route('admin.orders.index', ['status' => 'pending']), 'hint' => 'Sales'] : null,
        $can('manage_stock')      ? ['label' => 'Low stock items', 'icon' => 'warning', 'url' => route('admin.stock.index', ['filter' => 'low']), 'hint' => 'Catalog'] : null,
        ['label' => 'Open storefront', 'icon' => 'storefront', 'url' => route('home'), 'hint' => 'External', 'external' => true],
    ]));
    $quickActions = array_map(fn ($a) => array_merge($a, ['label' => __($a['label']), 'hint' => __($a['hint'])]), $quickActions);

    $paletteItems = [];
    foreach ($navGroups as $group) {
        foreach ($group['items'] as $item) {
            $paletteItems[] = ['label' => $item['label'], 'icon' => $item['icon'], 'url' => route($item['route']), 'hint' => $group['label']];
        }
    }
    $paletteItems = array_merge($paletteItems, $quickActions);

    $pageTitle = __(html_entity_decode(trim($__env->yieldContent('title', 'Admin Portal')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $btnPrimaryBg = $sysSettings['btn_primary_bg'] ?? '#0f172a';
    [$bpR, $bpG, $bpB] = array_map('intval', explode(' ', $hexToRgb($btnPrimaryBg)));
    $btnPrimaryIsDark = ((0.299 * $bpR + 0.587 * $bpG + 0.114 * $bpB) / 255) < 0.28;
    $initials  = collect(explode(' ', $authUser->name ?? 'Admin'))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $sidebarBg }}">
    <title>{{ $pageTitle }} · {{ $storeName }} Admin</title>

    {{-- Apply theme + sidebar state before first paint (prevents flashing) --}}
    <script>
        (function () {
            var d = document.documentElement, mode = 'system';
            try { mode = localStorage.getItem('admin_theme_mode') || '{{ $sysSettings['theme_mode'] ?? 'system' }}'; } catch (e) {}
            var dark = mode === 'dark' || (mode === 'system' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) d.classList.add('dark');
            try { if (localStorage.getItem('admin_sidebar_collapsed') === 'true') d.classList.add('sidebar-mini'); } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Vadodara:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Phosphor Icons (UI) + Font Awesome (category icons stored in the database) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/duotone/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: 'rgb(var(--c-primary) / <alpha-value>)',
                        hover: 'var(--theme-hover)',
                        sidebarBg: 'var(--sidebar-bg)',
                        sidebarActive: 'var(--sidebar-active)',
                        brand: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b', 950: '#022c22' },
                    },
                    fontFamily: { sans: ['"Plus Jakarta Sans"', '"Hind Vadodara"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    boxShadow: { soft: '0 1px 2px rgba(16,24,40,.04), 0 1px 3px rgba(16,24,40,.06)', lift: '0 12px 32px -12px rgba(15,23,42,.25)' },
                }
            }
        }
    </script>

    <style>
        :root {
            --theme-primary: {{ $primary }};
            --c-primary: {{ $hexToRgb($primary) }};
            --theme-primary-contrast: {{ $contrast($primary) }};
            --theme-hover: {{ $sysSettings['theme_hover_color'] ?? '#334155' }};
            --btn-primary-bg: {{ $sysSettings['btn_primary_bg'] ?? '#0f172a' }};
            --btn-primary-text: {{ $sysSettings['btn_primary_text'] ?? '#ffffff' }};
            --btn-primary-hover: {{ $sysSettings['btn_primary_hover'] ?? '#1e293b' }};
            --btn-accent-bg: {{ $sysSettings['btn_accent_bg'] ?? '#10b981' }};
            --btn-accent-text: {{ $sysSettings['btn_accent_text'] ?? '#ffffff' }};
            --sidebar-bg: {{ $sidebarBg }};
            --c-sidebar-text: {{ $hexToRgb($sidebarText, '255 255 255') }};
            --sidebar-text: {{ $sidebarText }};
            --sidebar-active: {{ $sidebarActive }};
            --sidebar-active-text: {{ $contrast($sidebarActive) }};
        }
        @if($btnPrimaryIsDark)
        /* A very dark primary button would vanish on dark surfaces: use a light variant in dark mode */
        .dark { --btn-primary-bg: #e2e8f0; --btn-primary-text: #0f172a; --btn-primary-hover: #ffffff; }
        @endif
    </style>
    <link rel="stylesheet" href="{{ asset('assets/shared/fx-select.css') }}?v=2.1.0">
    <link rel="stylesheet" href="{{ asset('assets/admin/admin.css') }}?v=2.1.0">
    @stack('styles')
</head>
<body class="admin-body">
    <div id="pageProgress" class="page-progress"></div>

    {{-- ============================== SIDEBAR ============================== --}}
    <aside id="adminSidebar" class="app-sidebar" aria-label="Main navigation">
        <div class="sb-brand">
            <a href="{{ route('admin.dashboard') }}" class="sb-brand-link">
                <span class="sb-logo"><i class="ph-fill ph-basket"></i></span>
                <span class="sb-brand-text">
                    <span class="sb-brand-name">{{ $storeName }}</span>
                    <span class="sb-brand-sub">{{ __('Admin Console') }}</span>
                </span>
            </a>
            <button type="button" class="sb-close" data-sidebar-close aria-label="Close menu"><i class="ph ph-x"></i></button>
        </div>

        <div class="sb-search">
            <i class="ph ph-magnifying-glass"></i>
            <input type="search" id="sidebarFilter" placeholder="{{ __('Filter menu…') }}" autocomplete="off" aria-label="Filter menu">
        </div>

        <nav class="sb-nav" id="sidebarNav">
            @foreach($navGroups as $group)
                @php $gKey = $group['key']; @endphp
                <div class="sb-group" data-group="{{ $gKey }}">
                    <button type="button" class="sb-group-title" data-group-toggle="{{ $gKey }}">
                        <span>{{ $group['label'] }}</span>
                        <i class="ph ph-caret-down"></i>
                    </button>
                    <ul class="sb-group-items">
                        @foreach($group['items'] as $item)
                            <li>
                                <a href="{{ route($item['route']) }}" class="sb-item {{ $item['isActive'] ? 'is-active' : '' }}" data-label="{{ strtolower($item['label']) }}" @if($item['isActive']) aria-current="page" @endif>
                                    <i class="sb-icon {{ $item['isActive'] ? 'ph-fill' : 'ph-duotone' }} ph-{{ $item['icon'] }}"></i>
                                    <span class="sb-label">{{ $item['label'] }}</span>
                                    @if(!empty($item['badge']))
                                        <span class="sb-badge sb-badge-{{ $item['badgeTone'] }}" title="{{ $item['badgeTitle'] ?? '' }}">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <p class="sb-empty hidden" id="sidebarEmpty">{{ __('No menu items match.') }}</p>
        </nav>

        <div class="sb-footer">
            <div class="sb-user">
                <span class="sb-avatar">{{ $initials }}</span>
                <span class="sb-user-meta">
                    <span class="sb-user-name">{{ $authUser->name ?? 'Administrator' }}</span>
                    <span class="sb-user-role">{{ $authUser->roleModel->display_name ?? ucfirst(str_replace('_', ' ', $authUser->role ?? 'admin')) }}</span>
                </span>
                <form action="{{ route('admin.logout') }}" method="POST" class="sb-logout-form">
                    @csrf
                    <button type="submit" class="sb-logout" title="Sign out" data-no-loading><i class="ph ph-sign-out"></i></button>
                </form>
            </div>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-close></div>

    {{-- ============================== MAIN ============================== --}}
    <div class="app-main">
        <header class="app-topbar">
            <div class="flex items-center gap-2 min-w-0">
                <button type="button" id="sidebarToggle" class="tb-btn" title="Toggle sidebar ( [ )" aria-label="Toggle sidebar">
                    <i class="ph ph-sidebar-simple text-xl"></i>
                </button>

                <nav class="tb-breadcrumb hidden md:flex" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="tb-crumb" title="Dashboard"><i class="ph ph-house"></i></a>
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @else
                        @if($activeGroup && $activeGroup !== 'Overview')
                            <i class="ph ph-caret-right tb-crumb-sep"></i>
                            <span class="tb-crumb">{{ $activeGroup }}</span>
                        @endif
                        @if($activeItem && $activeItem['label'] !== $pageTitle && $activeItem['route'] !== 'admin.dashboard')
                            <i class="ph ph-caret-right tb-crumb-sep"></i>
                            <a href="{{ route($activeItem['route']) }}" class="tb-crumb">{{ $activeItem['label'] }}</a>
                        @endif
                        <i class="ph ph-caret-right tb-crumb-sep"></i>
                        <span class="tb-crumb is-current">{{ $pageTitle }}</span>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-1 sm:gap-1.5">
                <button type="button" class="tb-search" data-palette-open aria-label="Search">
                    <i class="ph ph-magnifying-glass text-lg"></i>
                    <span class="hidden lg:inline">{{ __('Search or jump to…') }}</span>
                    <kbd class="hidden lg:inline-flex">Ctrl K</kbd>
                </button>

                {{-- Theme --}}
                <div class="relative" data-dropdown>
                    <button type="button" class="tb-btn" data-dropdown-toggle title="Appearance" aria-label="Appearance">
                        <i id="themeCurrentIcon" class="ph ph-desktop text-lg"></i>
                    </button>
                    <div class="tb-menu w-44" data-dropdown-menu>
                        <p class="tb-menu-title">{{ __('Appearance') }}</p>
                        <button type="button" class="tb-menu-item" data-theme-set="light"><i class="ph ph-sun"></i> {{ __('Light') }} <i class="ph-bold ph-check ml-auto tm-check"></i></button>
                        <button type="button" class="tb-menu-item" data-theme-set="dark"><i class="ph ph-moon-stars"></i> {{ __('Dark') }} <i class="ph-bold ph-check ml-auto tm-check"></i></button>
                        <button type="button" class="tb-menu-item" data-theme-set="system"><i class="ph ph-desktop"></i> {{ __('System') }} <i class="ph-bold ph-check ml-auto tm-check"></i></button>
                    </div>
                </div>

                {{-- Fullscreen --}}
                <button type="button" class="tb-btn" id="fullscreenToggle" title="Full screen (Ctrl+Shift+F)" aria-label="Toggle full screen">
                    <i class="ph ph-corners-out text-lg"></i>
                </button>

                {{-- Language --}}
                <span class="hidden sm:inline-flex">
                    <a href="{{ route('lang.switch', app()->getLocale() === 'gu' ? 'en' : 'gu') }}" class="tb-btn tb-btn-text" title="Switch language">
                        <i class="ph ph-translate text-lg"></i>
                        <span class="text-[11px] font-bold">{{ app()->getLocale() === 'gu' ? 'EN' : 'ગુજ' }}</span>
                    </a>
                </span>

                {{-- Notifications --}}
                <div class="relative" data-dropdown>
                    <button type="button" class="tb-btn relative" data-dropdown-toggle title="Notifications" aria-label="Notifications">
                        <i class="ph ph-bell text-lg"></i>
                        @if($notifTotal > 0)
                            <span class="tb-dot">{{ $notifTotal > 9 ? '9+' : $notifTotal }}</span>
                        @endif
                    </button>
                    <div class="tb-menu tb-menu-wide p-0" data-dropdown-menu>
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700/70">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Notifications') }}</p>
                            <span class="text-[11px] font-semibold text-slate-500">{{ __($notifTotal == 1 ? ':count alert' : ':count alerts', ['count' => $notifTotal]) }}</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto p-1.5">
                            @if($can('manage_orders'))
                                @foreach($notifOrders as $n)
                                    <a href="{{ route('admin.orders.show', $n) }}" class="notif-item">
                                        <span class="notif-icon bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="ph ph-shopping-cart-simple"></i></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ __('New order #:number', ['number' => $n->order_number]) }}</span>
                                            <span class="block text-[11px] text-slate-500 truncate">{{ $n->customer_name }} · ₹{{ number_format($n->total_amount, 2) }}</span>
                                        </span>
                                        <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $n->created_at->diffForHumans(null, true) }}</span>
                                    </a>
                                @endforeach
                            @endif
                            @if($can('manage_stock'))
                                @foreach($notifStock as $p)
                                    <a href="{{ route('admin.stock.index', ['filter' => $p->stock_quantity <= 0 ? 'out' : 'low']) }}" class="notif-item">
                                        <span class="notif-icon {{ $p->stock_quantity <= 0 ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' : 'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300' }}"><i class="ph ph-package"></i></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $p->name_en }}</span>
                                            <span class="block text-[11px] text-slate-500">{{ $p->stock_quantity <= 0 ? 'Out of stock' : $p->stock_quantity.' left · low stock' }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            @endif
                            @if($notifTotal === 0)
                                <div class="px-4 py-10 text-center">
                                    <i class="ph-duotone ph-bell-simple-slash text-4xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-2 text-xs font-semibold text-slate-500">{{ __('You\'re all caught up') }}</p>
                                </div>
                            @endif
                        </div>
                        @if($can('manage_orders') && $pendingCnt > 0)
                            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="block text-center text-xs font-bold py-2.5 border-t border-slate-100 dark:border-slate-700/70 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/40 rounded-b-2xl">{{ __('View all pending orders') }}</a>
                        @endif
                    </div>
                </div>

                {{-- Profile --}}
                <div class="relative" data-dropdown>
                    <button type="button" class="tb-profile" data-dropdown-toggle aria-label="Account menu">
                        <span class="tb-avatar">{{ $initials }}<span class="tb-online"></span></span>
                        <span class="hidden xl:block text-left leading-tight">
                            <span class="block text-xs font-bold text-slate-900 dark:text-white max-w-[9rem] truncate">{{ $authUser->name ?? 'Administrator' }}</span>
                            <span class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wide">{{ $authUser->roleModel->display_name ?? $authUser->role ?? 'Admin' }}</span>
                        </span>
                        <i class="ph ph-caret-down text-xs text-slate-400 hidden xl:block"></i>
                    </button>
                    <div class="tb-menu w-60" data-dropdown-menu>
                        <div class="px-3 py-2.5 mb-1 border-b border-slate-100 dark:border-slate-700/70">
                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $authUser->name ?? 'Administrator' }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $authUser->email ?? $authUser->phone }}</p>
                        </div>
                        <a href="{{ route('home') }}" target="_blank" class="tb-menu-item"><i class="ph ph-storefront"></i> {{ __('View storefront') }} <i class="ph ph-arrow-square-out ml-auto text-slate-400"></i></a>
                        @if($can('manage_settings'))
                            <a href="{{ route('admin.settings.index') }}" class="tb-menu-item"><i class="ph ph-gear-six"></i> {{ __('Settings') }}</a>
                        @endif
                        @if($can('manage_users') && $authUser)
                            <a href="{{ route('admin.users.edit', $authUser) }}" class="tb-menu-item"><i class="ph ph-user-circle-gear"></i> {{ __('My account') }}</a>
                        @endif
                        <a href="{{ route('lang.switch', app()->getLocale() === 'gu' ? 'en' : 'gu') }}" class="tb-menu-item"><i class="ph ph-translate"></i> {{ app()->getLocale() === 'gu' ? 'Switch to English' : 'ગુજરાતીમાં જુઓ' }}</a>
                        <button type="button" class="tb-menu-item" data-shortcuts-open><i class="ph ph-keyboard"></i> {{ __('Keyboard shortcuts') }} <kbd class="ml-auto">?</kbd></button>
                        <div class="my-1 border-t border-slate-100 dark:border-slate-700/70"></div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="tb-menu-item text-rose-600 dark:text-rose-400" data-no-loading><i class="ph ph-sign-out"></i> {{ __('Sign out') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-content" id="appContent">
            @if($errors->any())
                <div class="alert alert-danger mb-5" role="alert">
                    <i class="ph-fill ph-warning-circle text-lg"></i>
                    <div class="min-w-0">
                        <p class="font-bold">Please fix the following {{ $errors->count() > 1 ? $errors->count().' errors' : 'error' }}:</p>
                        <ul class="mt-1 list-disc list-inside text-[13px] space-y-0.5">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close" onclick="this.closest('.alert').remove()" aria-label="Dismiss"><i class="ph ph-x"></i></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="app-footer">
            <div>
                <span>{{ $sysSettings['footer_copyright_prefix'] ?? '© '.date('Y').', made with ❤️ by' }}</span>
                <a href="{{ $sysSettings['footer_creator_url'] ?? 'https://decentinfoways.com' }}" target="_blank" rel="noopener" class="font-bold text-slate-700 dark:text-slate-200 hover:underline">{{ $sysSettings['footer_creator_name'] ?? 'Decent Infoways' }}</a>
            </div>
            <div class="flex items-center gap-4">
                <button type="button" class="hover:text-slate-800 dark:hover:text-white" data-shortcuts-open>{{ __('Shortcuts') }}</button>
                <a href="{{ route('home') }}" target="_blank" class="hover:text-slate-800 dark:hover:text-white">{{ __('Storefront') }}</a>
                <span class="hidden sm:inline text-slate-400">v2.0</span>
            </div>
        </footer>
    </div>

    {{-- ============================== COMMAND PALETTE ============================== --}}
    <div id="commandPalette" class="cp-overlay hidden" role="dialog" aria-modal="true" aria-label="Command palette">
        <div class="cp-panel">
            <div class="cp-search">
                <i class="ph ph-magnifying-glass text-lg text-slate-400"></i>
                <input type="text" id="cpInput" placeholder="{{ __('Search pages and actions…') }}" autocomplete="off" spellcheck="false">
                <kbd>Esc</kbd>
            </div>
            <ul id="cpList" class="cp-list" role="listbox"></ul>
            <div class="cp-foot">
                <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
                <span><kbd>Enter</kbd> open</span>
                <span class="ml-auto hidden sm:inline">Tip: press <kbd>Ctrl</kbd>+<kbd>K</kbd> anywhere</span>
            </div>
        </div>
    </div>

    {{-- ============================== SHORTCUTS ============================== --}}
    <div id="shortcutsModal" class="cp-overlay hidden" role="dialog" aria-modal="true" aria-label="Keyboard shortcuts">
        <div class="cp-panel max-w-md p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2"><i class="ph-duotone ph-keyboard text-xl"></i> Keyboard shortcuts</h3>
                <button type="button" class="tb-btn" data-modal-close aria-label="Close"><i class="ph ph-x"></i></button>
            </div>
            <dl class="shortcut-list">
                <div><dt>{{ __('Search / command palette') }}</dt><dd><kbd>Ctrl</kbd><kbd>K</kbd></dd></div>
                <div><dt>{{ __('Toggle sidebar') }}</dt><dd><kbd>[</kbd></dd></div>
                <div><dt>{{ __('Toggle full screen') }}</dt><dd><kbd>Ctrl</kbd><kbd>Shift</kbd><kbd>F</kbd></dd></div>
                <div><dt>{{ __('Toggle dark mode') }}</dt><dd><kbd>Ctrl</kbd><kbd>Shift</kbd><kbd>L</kbd></dd></div>
                <div><dt>{{ __('Focus table search') }}</dt><dd><kbd>/</kbd></dd></div>
                <div><dt>{{ __('Show this help') }}</dt><dd><kbd>?</kbd></dd></div>
            </dl>
        </div>
    </div>

    <div id="toastStack" class="toast-stack" aria-live="polite"></div>
    <button type="button" id="backToTop" class="back-to-top" aria-label="Back to top"><i class="ph-bold ph-arrow-up"></i></button>

    {{-- ============================== SCRIPTS ============================== --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.js"></script>
    <script>
        window.AdminConfig = {
            storeName: @json($storeName),
            pageTitle: @json($pageTitle),
            userName: @json($authUser->name ?? 'Admin'),
            defaultTheme: @json($sysSettings['theme_mode'] ?? 'system'),
            palette: @json($paletteItems),
            locale: @json(app()->getLocale()),
            i18n: @json(app()->getLocale() === 'gu' ? json_decode(@file_get_contents(lang_path('gu.json')) ?: '{}', true) : new \stdClass),
            flash: {
                success: @json(session('success')),
                error: @json(session('error')),
                warning: @json(session('warning')),
                info: @json(session('info')),
            },
        };
    </script>
    <script src="{{ asset('assets/shared/fx-select.js') }}?v=2.1.0"></script>
    <script src="{{ asset('assets/admin/admin.js') }}?v=2.1.0"></script>
    @stack('scripts')
</body>
</html>
