@php
    use App\Models\Order;
    use App\Models\Product;
    use App\Models\Category;
    use App\Models\Slider;
    use App\Models\Offer;

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

    // 1. Orders notifications (Pending + Recent orders)
    $rawOrders = Order::latest()->take(6)->get();
    $orderNotifs = $rawOrders->map(function ($o) {
        $isPending = $o->order_status === 'pending';
        return [
            'id' => 'ord_' . $o->id,
            'category' => 'orders',
            'icon' => 'ph-shopping-bag',
            'icon_bg' => $isPending ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            'title' => 'Order #' . $o->order_number,
            'subtitle' => ($o->customer_name ?? 'Customer') . ' · ₹' . number_format($o->total_amount, 2) . ' · ' . ucfirst(str_replace('_', ' ', $o->order_status)),
            'url' => route('admin.orders.show', $o),
            'time' => $o->created_at,
            'is_unread' => $isPending || ($o->created_at && $o->created_at->gt(now()->subHours(24))),
        ];
    });

    // 2. Stock alerts
    $rawStock = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('stock_quantity')->take(5)->get();
    $stockNotifs = $rawStock->map(function ($p) {
        $isOut = $p->stock_quantity <= 0;
        return [
            'id' => 'stk_' . $p->id,
            'category' => 'stock',
            'icon' => 'ph-package',
            'icon_bg' => $isOut ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' : 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
            'title' => $p->name_en . ' (' . ($isOut ? 'Out of Stock' : 'Low Stock') . ')',
            'subtitle' => $isOut ? 'Inventory is completely empty' : $p->stock_quantity . ' units left (threshold: ' . $p->low_stock_threshold . ')',
            'url' => route('admin.stock.index', ['filter' => $isOut ? 'out' : 'low']),
            'time' => $p->updated_at ?? $p->created_at ?? now(),
            'is_unread' => true,
        ];
    });

    // 3. Catalog updates (Recent Category, Product, Slider changes)
    $recentCats = Category::latest('updated_at')->take(3)->get()->map(function ($c) {
        return [
            'id' => 'cat_' . $c->id . '_' . ($c->updated_at ? $c->updated_at->timestamp : 0),
            'category' => 'catalog',
            'icon' => 'ph-stack',
            'icon_bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'title' => 'Category: ' . $c->name_en,
            'subtitle' => ($c->status ? 'Active' : 'Inactive') . ' · ' . ($c->created_at && $c->updated_at && $c->created_at->eq($c->updated_at) ? 'Created' : 'Updated'),
            'url' => route('admin.categories.edit', $c),
            'time' => $c->updated_at ?? $c->created_at,
            'is_unread' => ($c->updated_at ?? $c->created_at)?->gt(now()->subHours(24)) ?? false,
        ];
    });

    $recentProds = Product::latest('updated_at')->take(4)->get()->map(function ($p) {
        return [
            'id' => 'prd_' . $p->id . '_' . ($p->updated_at ? $p->updated_at->timestamp : 0),
            'category' => 'catalog',
            'icon' => 'ph-tag',
            'icon_bg' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300',
            'title' => 'Product: ' . $p->name_en,
            'subtitle' => '₹' . number_format($p->price, 2) . ' · ' . ($p->status ? 'Active' : 'Draft'),
            'url' => route('admin.products.edit', $p),
            'time' => $p->updated_at ?? $p->created_at,
            'is_unread' => ($p->updated_at ?? $p->created_at)?->gt(now()->subHours(24)) ?? false,
        ];
    });

    $recentSliders = Slider::latest('updated_at')->take(2)->get()->map(function ($s) {
        return [
            'id' => 'sld_' . $s->id . '_' . ($s->updated_at ? $s->updated_at->timestamp : 0),
            'category' => 'catalog',
            'icon' => 'ph-slideshow',
            'icon_bg' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            'title' => 'Slider: ' . ($s->title_en ?: 'Banner #' . $s->id),
            'subtitle' => ($s->status ? 'Active' : 'Inactive') . ' banner',
            'url' => route('admin.sliders.edit', $s),
            'time' => $s->updated_at ?? $s->created_at,
            'is_unread' => ($s->updated_at ?? $s->created_at)?->gt(now()->subHours(24)) ?? false,
        ];
    });

    // Merge and sort
    $allNotifFeed = $orderNotifs->concat($stockNotifs)->concat($recentCats)->concat($recentProds)->concat($recentSliders)
        ->sortByDesc(fn ($item) => $item['time'] ? $item['time']->timestamp : 0)
        ->values();

    $unreadFeedCount = $allNotifFeed->where('is_unread', true)->count();
    $notifTotal = $unreadFeedCount > 0 ? $unreadFeedCount : ($pendingCnt + $lowStockCnt + $outStockCnt);

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
    <div id="toastStack" class="toast-stack" aria-live="polite" aria-atomic="true"></div>

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
                      {{-- Notifications --}}
                <div class="relative" data-dropdown>
                    <button type="button" class="tb-btn relative" data-dropdown-toggle title="Notifications" aria-label="Notifications" id="adminNotifBellBtn">
                        <i class="ph ph-bell text-lg"></i>
                        <span class="tb-dot {{ $notifTotal > 0 ? '' : 'hidden' }}" id="notifBadgeCount">{{ $notifTotal > 9 ? '9+' : ($notifTotal > 0 ? $notifTotal : '') }}</span>
                    </button>
                    <div class="tb-menu tb-menu-wide p-0 w-80 sm:w-[390px] shadow-2xl rounded-2xl overflow-hidden" data-dropdown-menu id="adminNotifDropdown">
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/70">
                            <div class="flex items-center gap-2">
                                <i class="ph-fill ph-bell-ringing text-emerald-500 text-base"></i>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Notifications & Activity') }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300" id="notifHeaderCount">{{ $notifTotal }} {{ __('new') }}</span>
                            </div>
                            <button type="button" class="text-[11px] font-semibold text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition" onclick="markAllNotificationsRead()">
                                <i class="ph ph-checks mr-1"></i>{{ __('Mark all read') }}
                            </button>
                        </div>

                        {{-- Filter Tabs --}}
                        <div class="flex items-center gap-1 px-3 py-2 border-b border-slate-100 dark:border-slate-700/60 bg-white dark:bg-slate-900 text-xs overflow-x-auto">
                            <button type="button" class="notif-filter-tab is-active" data-notif-tab="all">{{ __('All') }} ({{ count($allNotifFeed) }})</button>
                            <button type="button" class="notif-filter-tab" data-notif-tab="orders">{{ __('Orders') }} ({{ count($orderNotifs) }})</button>
                            <button type="button" class="notif-filter-tab" data-notif-tab="stock">{{ __('Stock') }} ({{ count($stockNotifs) }})</button>
                            <button type="button" class="notif-filter-tab" data-notif-tab="catalog">{{ __('Catalog') }} ({{ count($recentCats) + count($recentProds) + count($recentSliders) }})</button>
                        </div>

                        {{-- List Container --}}
                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100/70 dark:divide-slate-800/70 p-1" id="notifListContainer">
                            @forelse($allNotifFeed as $item)
                                <a href="{{ $item['url'] }}" class="notif-card flex items-start gap-3 p-2.5 rounded-xl transition hover:bg-slate-50 dark:hover:bg-slate-800/50 {{ $item['is_unread'] ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}" data-notif-cat="{{ $item['category'] }}" data-notif-id="{{ $item['id'] }}">
                                    <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-base {{ $item['icon_bg'] }}">
                                        <i class="ph {{ $item['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $item['title'] }}</span>
                                            <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $item['time'] ? $item['time']->diffForHumans(null, true) : '' }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $item['subtitle'] }}</p>
                                    </div>
                                    @if($item['is_unread'])
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 mt-2 notif-dot"></span>
                                    @endif
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center" id="notifEmptyState">
                                    <i class="ph-duotone ph-bell-simple-slash text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-2 text-xs font-semibold text-slate-500">{{ __('You\'re all caught up') }}</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Footer Quick Actions --}}
                        <div class="grid grid-cols-2 divide-x divide-slate-100 dark:divide-slate-700/60 border-t border-slate-100 dark:border-slate-700/70 bg-slate-50/50 dark:bg-slate-800/40 text-[11px] font-bold text-center">
                            @if($can('manage_orders'))
                                <a href="{{ route('admin.orders.index') }}" class="py-2.5 px-3 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/50 flex items-center justify-center gap-1.5 transition">
                                    <i class="ph ph-shopping-bag"></i> {{ __('View Orders') }}
                                </a>
                            @endif
                            @if($can('manage_stock'))
                                <a href="{{ route('admin.stock.index') }}" class="py-2.5 px-3 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/50 flex items-center justify-center gap-1.5 transition">
                                    <i class="ph ph-warehouse"></i> {{ __('Stock Alerts') }}
                                </a>
                            @endif
                        </div>
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

    {{-- ============================== LIVE CUSTOMIZER WIDGET & DRAWER ============================== --}}
    <!-- Floating Draggable Customizer Button -->
    <div id="customizerWidget" class="customizer-widget" style="position:fixed;bottom:28px;right:28px;z-index:99999;touch-action:none;user-select:none;">
        <!-- Continuous Seamless Radar Aura Waves (No breaks) -->
        <div class="customizer-radar-wave wave-1"></div>
        <div class="customizer-radar-wave wave-2"></div>
        <div class="customizer-radar-wave wave-3"></div>
        <div class="customizer-breathing-core"></div>

        <button type="button" id="customizerBtn" onclick="window.toggleCustomizerDrawer && window.toggleCustomizerDrawer(event)" class="customizer-circle-btn" aria-label="Open Customizer" title="Customize Theme & Layout">
            <i class="ph-duotone ph-gear-six customizer-gear"></i>
        </button>
    </div>

    <!-- Customizer Drawer Modal (Matching Screenshot 2) -->
    <div id="customizerDrawer" class="customizer-backdrop is-hidden" aria-hidden="true">
        <div class="customizer-panel" role="dialog" aria-modal="true" aria-label="Customizer">
            <!-- Header -->
            <div class="customizer-header">
                <div class="flex items-center gap-2.5 font-black text-slate-800 dark:text-white text-base">
                    <i class="ph-duotone ph-gear-six text-2xl text-emerald-600 dark:text-emerald-400"></i>
                    <span class="text-base font-extrabold tracking-tight">Customizer</span>
                </div>
                <button type="button" id="customizerClose" onclick="window.closeCustomizerDrawer && window.closeCustomizerDrawer()" class="customizer-close-btn" aria-label="Close Customizer">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Body Options -->
            <div class="customizer-body">
                <!-- 1. APPEARANCE -->
                <div class="customizer-section">
                    <label class="customizer-label">
                        <i class="ph ph-sun text-sm"></i> APPEARANCE
                    </label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <button type="button" class="customizer-opt-btn" data-cust-theme="light">
                            <i class="ph ph-sun text-xl text-amber-500"></i>
                            <span>Light</span>
                            <i class="ph-bold ph-check opt-check"></i>
                        </button>
                        <button type="button" class="customizer-opt-btn" data-cust-theme="dark">
                            <i class="ph ph-moon text-xl text-indigo-400"></i>
                            <span>Dark</span>
                            <i class="ph-bold ph-check opt-check"></i>
                        </button>
                        <button type="button" class="customizer-opt-btn" data-cust-theme="system">
                            <i class="ph ph-desktop text-xl text-slate-400"></i>
                            <span>Auto</span>
                            <i class="ph-bold ph-check opt-check"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. LANGUAGE -->
                <div class="customizer-section">
                    <label class="customizer-label">
                        <i class="ph ph-translate text-sm"></i> LANGUAGE
                    </label>
                    <div class="space-y-2">
                        <a href="{{ route('lang.switch', 'en') }}" class="customizer-lang-btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-xs font-black bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded-md">US</span>
                                <span class="font-bold text-sm text-slate-900 dark:text-white">English</span>
                            </div>
                            @if(app()->getLocale() === 'en')
                                <i class="ph-bold ph-check text-emerald-600 dark:text-emerald-400 text-base"></i>
                            @endif
                        </a>
                        <a href="{{ route('lang.switch', 'gu') }}" class="customizer-lang-btn {{ app()->getLocale() === 'gu' ? 'is-active' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-xs font-black bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded-md">GU</span>
                                <span class="font-bold text-sm text-slate-900 dark:text-white">ગુજરાતી (Gujarati)</span>
                            </div>
                            @if(app()->getLocale() === 'gu')
                                <i class="ph-bold ph-check text-emerald-600 dark:text-emerald-400 text-base"></i>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- 4. VIEW OPTIONS -->
                <div class="customizer-section">
                    <label class="customizer-label">
                        <i class="ph ph-arrows-out text-sm"></i> VIEW OPTIONS
                    </label>
                    <button type="button" id="fullscreenToggleBtn" class="customizer-full-btn">
                        <i class="ph ph-corners-out text-lg"></i>
                        <span id="fullscreenLabel">Go Full Screen</span>
                    </button>
                </div>
            </div>

            <!-- Footer Tag -->
            <div class="customizer-footer">
                <span>{{ $storeName }} v2.0</span>
            </div>
        </div>
    </div>

    <style>
        /* Floating Draggable Widget */
        .customizer-widget {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .customizer-circle-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #1b5e52; /* Dark teal matching screenshot */
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 25px rgba(27, 94, 82, 0.45), 0 4px 12px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            cursor: grab;
            position: relative;
            z-index: 5;
            transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s, box-shadow 0.2s;
        }

        .customizer-circle-btn:active {
            cursor: grabbing;
            transform: scale(0.94);
        }

        /* Faster, Crisp Clockwise Right Spin on Hover */
        .customizer-gear {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .customizer-circle-btn:hover .customizer-gear {
            animation: spinGearFastClockwise 0.55s linear infinite;
        }

        @keyframes spinGearFastClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Seamless Continuous Zoom-in / Zoom-out Radar Aura Wave Effect (No breaks) */
        .customizer-breathing-core {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(27, 94, 82, 0.5);
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 1;
            animation: customizerCoreGlow 2s ease-in-out infinite alternate;
        }

        @keyframes customizerCoreGlow {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.7;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.35);
                opacity: 0.25;
            }
        }

        .customizer-radar-wave {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(27, 94, 82, 0.45);
            transform: translate(-50%, -50%) scale(1);
            pointer-events: none;
            z-index: 2;
            animation: customizerContinuousPulse 3s cubic-bezier(0.1, 0.6, 0.2, 1) infinite;
        }

        .customizer-radar-wave.wave-1 { animation-delay: 0s; }
        .customizer-radar-wave.wave-2 { animation-delay: 1s; }
        .customizer-radar-wave.wave-3 { animation-delay: 2s; }

        @keyframes customizerContinuousPulse {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.85;
            }
            50% {
                opacity: 0.4;
            }
            100% {
                transform: translate(-50%, -50%) scale(2.4);
                opacity: 0;
            }
        }

        /* Customizer Backdrop & Drawer Panel */
        .customizer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 999999;
            display: flex;
            justify-content: flex-end;
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s;
        }

        .customizer-backdrop.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .customizer-panel {
            width: 330px;
            max-width: 90vw;
            height: 100%;
            background: #ffffff;
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            transform: translateX(0);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .customizer-backdrop.is-hidden .customizer-panel {
            transform: translateX(100%);
        }

        .dark .customizer-panel {
            background: #0f172a;
            color: #ffffff;
            border-left: 1px solid #1e293b;
        }

        .customizer-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark .customizer-header {
            border-color: #1e293b;
        }

        .customizer-close-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .customizer-close-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .dark .customizer-close-btn:hover {
            background: #1e293b;
            color: #ffffff;
            border-color: #334155;
        }

        .customizer-body {
            flex: 1;
            overflow-y: auto;
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .customizer-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .customizer-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .customizer-opt-btn {
            padding: 14px 10px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .dark .customizer-opt-btn {
            background: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .customizer-opt-btn:hover {
            border-color: #10b981;
            color: #10b981;
            background: #f0fdf4;
        }

        .dark .customizer-opt-btn:hover {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .customizer-opt-btn.is-active {
            border-color: #10b981 !important;
            background: #ecfdf5 !important;
            color: #059669 !important;
            box-shadow: 0 0 0 1px #10b981;
        }

        .dark .customizer-opt-btn.is-active {
            background: rgba(16, 185, 129, 0.18) !important;
            color: #34d399 !important;
        }

        .opt-check {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 11px;
            color: #10b981;
            display: none;
        }

        .customizer-opt-btn.is-active .opt-check {
            display: block;
        }

        .customizer-lang-btn {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            transition: all 0.2s;
        }

        .dark .customizer-lang-btn {
            background: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .customizer-lang-btn:hover {
            border-color: #10b981;
        }

        .customizer-lang-btn.is-active {
            border-color: #10b981 !important;
            background: #ecfdf5 !important;
            color: #059669 !important;
            box-shadow: 0 0 0 1px #10b981;
        }

        .dark .customizer-lang-btn.is-active {
            background: rgba(16, 185, 129, 0.18) !important;
            color: #34d399 !important;
        }

        .customizer-full-btn {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dark .customizer-full-btn {
            background: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .customizer-full-btn:hover {
            border-color: #10b981;
            color: #10b981;
            background: #f0fdf4;
        }

        .dark .customizer-full-btn:hover {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .customizer-footer {
            padding: 16px 22px;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-align: center;
        }

        .dark .customizer-footer {
            border-color: #1e293b;
        }
    </style>

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
    <script>
        // ==================== LIVE CUSTOMIZER CONTROLLER ====================
        (function() {
            var hasMoved = false;

            window.openCustomizerDrawer = function() {
                var drawer = document.getElementById('customizerDrawer');
                if (drawer) {
                    drawer.classList.remove('is-hidden');
                    drawer.setAttribute('aria-hidden', 'false');
                    syncActiveStates();
                }
            };

            window.closeCustomizerDrawer = function() {
                var drawer = document.getElementById('customizerDrawer');
                if (drawer) {
                    drawer.classList.add('is-hidden');
                    drawer.setAttribute('aria-hidden', 'true');
                }
            };

            window.toggleCustomizerDrawer = function(e) {
                if (hasMoved) return;
                var drawer = document.getElementById('customizerDrawer');
                if (!drawer) return;
                if (drawer.classList.contains('is-hidden')) {
                    window.openCustomizerDrawer();
                } else {
                    window.closeCustomizerDrawer();
                }
            };

            function syncActiveStates() {
                var currentTheme = localStorage.getItem('admin_theme_mode') || 'system';
                document.querySelectorAll('[data-cust-theme]').forEach(function(el) {
                    var val = el.getAttribute('data-cust-theme');
                    el.classList.toggle('is-active', val === currentTheme);
                });

                var fsBtn = document.getElementById('fullscreenToggleBtn');
                var fsLabel = document.getElementById('fullscreenLabel');
                if (fsBtn && fsLabel) {
                    var isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
                    fsLabel.textContent = isFs ? 'Exit Full Screen' : 'Go Full Screen';
                    var icon = fsBtn.querySelector('i');
                    if (icon) icon.className = isFs ? 'ph ph-corners-in text-lg' : 'ph ph-corners-out text-lg';
                }
            }

            function initLiveCustomizer() {
                var widget = document.getElementById('customizerWidget');
                var btn = document.getElementById('customizerBtn');
                var drawer = document.getElementById('customizerDrawer');
                var closeBtn = document.getElementById('customizerClose');
                var fsBtn = document.getElementById('fullscreenToggleBtn');
                if (!widget || !btn || !drawer) return;

                // 1. Restore saved position
                try {
                    var savedX = localStorage.getItem('customizer_pos_x');
                    var savedY = localStorage.getItem('customizer_pos_y');
                    if (savedX !== null && savedY !== null) {
                        var x = Math.max(10, Math.min(window.innerWidth - 65, parseInt(savedX, 10)));
                        var y = Math.max(10, Math.min(window.innerHeight - 65, parseInt(savedY, 10)));
                        widget.style.left = x + 'px';
                        widget.style.top = y + 'px';
                        widget.style.right = 'auto';
                        widget.style.bottom = 'auto';
                    }
                } catch(e) {}

                // 2. Drag & Drop logic (Mouse & Touch)
                var isDragging = false;
                var startX = 0, startY = 0;
                var initialLeft = 0, initialTop = 0;

                function onPointerDown(e) {
                    if (e.type === 'mousedown' && e.button !== 0) return;
                    var clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    var clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    
                    var rect = widget.getBoundingClientRect();
                    startX = clientX;
                    startY = clientY;
                    initialLeft = rect.left;
                    initialTop = rect.top;
                    isDragging = true;
                    hasMoved = false;

                    document.addEventListener('mousemove', onPointerMove, { passive: false });
                    document.addEventListener('mouseup', onPointerUp);
                    document.addEventListener('touchmove', onPointerMove, { passive: false });
                    document.addEventListener('touchend', onPointerUp);
                }

                function onPointerMove(e) {
                    if (!isDragging) return;
                    var clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    var clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    var dx = clientX - startX;
                    var dy = clientY - startY;

                    if (Math.hypot(dx, dy) > 6) {
                        hasMoved = true;
                        if (e.cancelable) e.preventDefault();
                        
                        var newX = initialLeft + dx;
                        var newY = initialTop + dy;
                        newX = Math.max(10, Math.min(window.innerWidth - widget.offsetWidth - 10, newX));
                        newY = Math.max(10, Math.min(window.innerHeight - widget.offsetHeight - 10, newY));

                        widget.style.left = newX + 'px';
                        widget.style.top = newY + 'px';
                        widget.style.right = 'auto';
                        widget.style.bottom = 'auto';
                    }
                }

                function onPointerUp(e) {
                    if (!isDragging) return;
                    isDragging = false;
                    document.removeEventListener('mousemove', onPointerMove);
                    document.removeEventListener('mouseup', onPointerUp);
                    document.removeEventListener('touchmove', onPointerMove);
                    document.removeEventListener('touchend', onPointerUp);

                    if (hasMoved) {
                        try {
                            var rect = widget.getBoundingClientRect();
                            localStorage.setItem('customizer_pos_x', Math.round(rect.left));
                            localStorage.setItem('customizer_pos_y', Math.round(rect.top));
                        } catch(e) {}
                        setTimeout(function() { hasMoved = false; }, 80);
                    }
                }

                btn.addEventListener('mousedown', onPointerDown);
                btn.addEventListener('touchstart', onPointerDown, { passive: true });

                drawer.addEventListener('click', function(e) {
                    if (e.target === drawer) window.closeCustomizerDrawer();
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !drawer.classList.contains('is-hidden')) window.closeCustomizerDrawer();
                });

                // 3. Theme switch
                document.querySelectorAll('[data-cust-theme]').forEach(function(themeBtn) {
                    themeBtn.addEventListener('click', function() {
                        var mode = themeBtn.getAttribute('data-cust-theme');
                        if (window.AdminTheme && window.AdminTheme.set) {
                            window.AdminTheme.set(mode);
                        } else {
                            localStorage.setItem('admin_theme_mode', mode);
                            var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                            document.documentElement.classList.toggle('dark', dark);
                        }
                        syncActiveStates();
                    });
                });

                // 4. Fullscreen
                if (fsBtn) {
                    fsBtn.addEventListener('click', function() {
                        if (document.fullscreenElement || document.webkitFullscreenElement) {
                            if (document.exitFullscreen) document.exitFullscreen();
                            else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                        } else {
                            var elem = document.documentElement;
                            if (elem.requestFullscreen) elem.requestFullscreen();
                            else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                        }
                    });
                    document.addEventListener('fullscreenchange', syncActiveStates);
                    document.addEventListener('webkitfullscreenchange', syncActiveStates);
                }

                try {
                    var savedDir = localStorage.getItem('customizer_direction');
                    if (savedDir) document.documentElement.setAttribute('dir', savedDir);
                } catch(e) {}

                syncActiveStates();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initLiveCustomizer);
            } else {
                initLiveCustomizer();
            }
        })();

        // ------------------------------------------------------------------
        // Notifications & Dynamic Read/Unread State Interactivity
        // ------------------------------------------------------------------
        (function() {
            var readNotifs = [];
            try {
                readNotifs = JSON.parse(localStorage.getItem('admin_read_notif_ids') || '[]');
                if (!Array.isArray(readNotifs)) readNotifs = [];
            } catch(e) {
                readNotifs = [];
            }

            var cards = document.querySelectorAll('.notif-card');
            
            // Sync read/unread states from localStorage
            cards.forEach(function(card) {
                var notifId = card.getAttribute('data-notif-id');
                if (notifId && readNotifs.indexOf(notifId) !== -1) {
                    card.classList.add('is-read');
                    var dot = card.querySelector('.notif-dot');
                    if (dot) dot.remove();
                }

                // Click on single notification marks it as read immediately
                card.addEventListener('click', function() {
                    if (notifId && readNotifs.indexOf(notifId) === -1) {
                        readNotifs.push(notifId);
                        try {
                            localStorage.setItem('admin_read_notif_ids', JSON.stringify(readNotifs));
                        } catch(e) {}
                        card.classList.add('is-read');
                        var dot = card.querySelector('.notif-dot');
                        if (dot) dot.remove();
                        updateUnreadBadge();
                    }
                });
            });

            function updateUnreadBadge() {
                var unreadCount = document.querySelectorAll('.notif-card:not(.is-read)').length;
                var badge = document.getElementById('notifBadgeCount');
                if (badge) {
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
                var headerCount = document.getElementById('notifHeaderCount');
                if (headerCount) {
                    headerCount.textContent = unreadCount + ' new';
                }
            }

            updateUnreadBadge();

            // When bell is clicked to open dropdown, mark current items as seen
            var bellBtn = document.getElementById('adminNotifBellBtn');
            if (bellBtn) {
                bellBtn.addEventListener('click', function() {
                    // Clicking the bell clears the badge dot
                    var badge = document.getElementById('notifBadgeCount');
                    if (badge) badge.classList.add('hidden');
                });
            }

            // Notification Filter Tabs
            document.querySelectorAll('[data-notif-tab]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var tab = btn.getAttribute('data-notif-tab');
                    document.querySelectorAll('[data-notif-tab]').forEach(function(b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');

                    var allCards = document.querySelectorAll('.notif-card');
                    var visibleCount = 0;
                    allCards.forEach(function(c) {
                        var cat = c.getAttribute('data-notif-cat');
                        if (tab === 'all' || cat === tab) {
                            c.style.display = 'flex';
                            visibleCount++;
                        } else {
                            c.style.display = 'none';
                        }
                    });
                    var empty = document.getElementById('notifEmptyState');
                    if (empty) {
                        empty.style.display = visibleCount === 0 ? 'block' : 'none';
                    }
                });
            });

            // Mark all read handler
            window.markAllNotificationsRead = function() {
                var allCards = document.querySelectorAll('.notif-card');
                allCards.forEach(function(c) {
                    var id = c.getAttribute('data-notif-id');
                    if (id && readNotifs.indexOf(id) === -1) {
                        readNotifs.push(id);
                    }
                    c.classList.add('is-read');
                    var dot = c.querySelector('.notif-dot');
                    if (dot) dot.remove();
                });
                try {
                    localStorage.setItem('admin_read_notif_ids', JSON.stringify(readNotifs));
                } catch(e) {}
                updateUnreadBadge();
                if (window.AdminToast) {
                    window.AdminToast.show('info', 'All notifications marked as read', 'Notifications');
                }
            };
        })();
    </script>
    @stack('scripts')
</body>
</html>
