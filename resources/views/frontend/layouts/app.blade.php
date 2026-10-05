@php
    $gu = app()->getLocale() === 'gu';
    // Rendered inside the Flutter app's WebView: hide the site chrome (header, footer, tab bar)
    $inApp = str_contains((string) request()->userAgent(), 'FreshExpressApp') || request()->boolean('in_app');
    $navCategories = \App\Models\Category::with('subCategories')->where('is_active', true)->orderBy('sort_order')->get();
    $authUser = Auth::user();
    $userInitial = $authUser ? strtoupper(mb_substr(trim($authUser->name ?: 'U'), 0, 1)) : '';
    $firstName = $authUser ? (\Illuminate\Support\Str::of(trim((string) $authUser->name))->before(' ')->limit(14)->toString() ?: __('messages.my_account')) : '';
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register')
        : (\Illuminate\Support\Facades\Route::has('customer.register') ? route('customer.register') : route('customer.login'));
    // CMS pages that actually exist (no broken links); "Contact & Help" jumps to the support block in the footer
    $activePageSlugs = \App\Models\Page::where('is_active', true)->pluck('slug');
    $infoLinks = collect([
        ['about-us', 'ph-info', __('messages.about_us')],
        ['privacy-policy', 'ph-shield-check', __('messages.privacy_policy')],
    ])->filter(fn($l) => $activePageSlugs->contains($l[0]))->map(fn($l) => [route('pages.show', $l[0]), $l[1], $l[2]])->values()->all();
    $infoLinks[] = ['#fxSupport', 'ph-headset', __('messages.contact_help')];
    $navSlot = \App\Models\Order::determineDeliverySlot();
    $navSlotShort = $navSlot['type'] === 'two_hours'
        ? ($gu ? '૨ કલાકમાં ડિલિવરી' : 'Delivery in 2 hours')
        : ($gu ? 'આવતીકાલે સવારે' : 'Tomorrow, 9 AM – 12 PM');
    $cartQty = Auth::check()
        ? (int) \App\Models\CartItem::where('user_id', Auth::id())->sum('quantity')
        : (int) \App\Models\CartItem::where('session_id', Session::getId())->sum('quantity');
    $wishCount = Auth::check() ? Auth::user()->wishlists()->count() : 0;
    $flash = collect(['success', 'info'])->filter(fn($k) => session($k))->map(fn($k) => ['type' => $k, 'message' => is_string(session($k)) ? __(session($k)) : session($k)])->values();
    $fxI18n = $gu ? [
                'item' => 'વસ્તુ', 'items' => 'વસ્તુઓ', 'view_cart' => 'કાર્ટ જુઓ', 'remove' => 'દૂર કરો',
                'cart_empty' => __('messages.your_cart_is_empty'), 'cart_empty_sub' => 'તાજાં ફળો, શાકભાજી અને રોજિંદી જરૂરિયાતો એક ટેપ દૂર છે.',
                'start_shopping' => __('messages.start_shopping'), 'add_more_for_free' => 'મફત ડિલિવરી માટે :amount વધુ ઉમેરો',
                'free_unlocked' => 'તમને મફત ડિલિવરી મળી ગઈ!', 'cart_error' => 'કાર્ટમાં ઉમેરી શકાયું નહીં', 'cart_update_error' => 'અપડેટ થઈ શક્યું નહીં',
                'max_stock' => 'સ્ટોકમાં ફક્ત :n ઉપલબ્ધ છે', 'login_for_wishlist' => 'વિશલિસ્ટ માટે લૉગિન કરો', 'wishlist_error' => 'વિશલિસ્ટ અપડેટ થઈ શક્યું નહીં',
                'off' => __('messages.off'), 'generic_error' => 'કંઈક ખોટું થયું', 'code_copied' => 'કોડ :code કૉપિ થયો', 'copied' => 'કૉપિ થયો!',
                'confirm_delete' => 'શું તમે ખરેખર કાઢી નાખવા માંગો છો?', 'confirm_delete_text' => 'આ ક્રિયા પાછી ફેરવી શકાશે નહીં.', 'delete' => 'કાઢી નાખો', 'cancel' => 'રદ કરો', 'select_search' => __('messages.select_search'), 'close' => __('messages.close'),
            ] : [
                'item' => 'item', 'items' => 'items', 'view_cart' => 'View cart', 'remove' => 'Remove',
                'cart_empty' => __('messages.your_cart_is_empty'), 'cart_empty_sub' => 'Fresh fruits, veggies and daily essentials are a tap away.',
                'start_shopping' => __('messages.start_shopping'), 'add_more_for_free' => 'Add :amount more for FREE delivery',
                'free_unlocked' => 'You unlocked FREE delivery!', 'cart_error' => 'Could not add to cart', 'cart_update_error' => 'Could not update item',
                'max_stock' => 'Only :n available in stock', 'login_for_wishlist' => 'Please login to save items to your wishlist', 'wishlist_error' => 'Could not update wishlist',
                'off' => 'OFF', 'generic_error' => 'Something went wrong', 'code_copied' => 'Code :code copied', 'copied' => 'Copied!',
                'confirm_delete' => 'Delete this item?', 'confirm_delete_text' => 'This action cannot be undone.', 'delete' => 'Delete', 'cancel' => 'Cancel', 'select_search' => __('messages.select_search'), 'close' => __('messages.close'),
            ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', __('messages.store_name')) - {{ __('messages.tagline') }}</title>

    {{-- Apply the saved theme before first paint (light / dark / system) --}}
    <script>
        (function () {
            var m = 'system';
            try { m = localStorage.getItem('theme') || 'system'; } catch (e) {}
            var dark = m === 'dark' || (m === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Vadodara:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons: Phosphor (UI) + Font Awesome (category icons stored in the DB) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/duotone/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet (map address picker) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Storefront components (loaded before Tailwind so utilities win) -->
    <link rel="stylesheet" href="{{ asset('assets/shared/fx-select.css') }}?v=2.2.0">
    <link rel="stylesheet" href="{{ asset('assets/front/store.css') }}?v=4">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b', 950: '#022c22' },
                        primary: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b', 950: '#022c22' }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Hind Vadodara"', 'system-ui', 'sans-serif']
                    },
                    screens: { xs: '400px' }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="fx-body min-h-full flex flex-col antialiased {{ $inApp ? 'fx-in-app' : 'fx-pb-tabbar' }} selection:bg-brand-500 selection:text-white">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] fx-btn fx-btn-primary">{{ $gu ? 'મુખ્ય સામગ્રી પર જાઓ' : 'Skip to content' }}</a>

    <!-- ===================== Utility bar ===================== -->
    <div class="fx-site-chrome fx-topbar hidden sm:block text-[12px] font-medium">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 min-w-0">
                <i class="ph-fill ph-lightning text-amber-300"></i>
                <span class="font-bold whitespace-nowrap">{{ trim(str_replace('⚡', '', __('messages.delivery_promise_title'))) }}</span>
                <span class="hidden md:inline text-brand-100/80 truncate">· {{ __('messages.delivery_promise_desc') }}</span>
            </div>
            <div class="flex items-center gap-0.5 shrink-0">
                <a href="{{ route('order.track') }}" class="fx-topbar-link hidden md:inline-flex">
                    <i class="ph ph-package"></i><span>{{ __('messages.track_order') }}</span>
                </a>
                <span class="hidden md:block w-px h-3.5 bg-white/20 mx-1" aria-hidden="true"></span>
                <div class="fx-dropdown">
                    <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" class="fx-topbar-link font-bold" aria-label="{{ __('messages.language') }}: {{ $gu ? 'ગુજરાતી' : 'English' }}">
                        <i class="ph ph-translate"></i><span>{{ $gu ? 'ગુજરાતી' : 'English' }}</span><i class="ph-bold ph-caret-down text-[10px] opacity-70"></i>
                    </button>
                    <div class="fx-dropdown-menu !min-w-[12.5rem]" role="menu">
                        <p class="fx-menu-heading">{{ __('messages.language') }}</p>
                        <a href="{{ route('lang.switch', 'en') }}" lang="en" role="menuitemradio" aria-checked="{{ $gu ? 'false' : 'true' }}" class="fx-menu-item {{ $gu ? '' : 'is-active' }}"><span class="fx-lang-chip">EN</span>English<i class="ph-bold ph-check fx-check"></i></a>
                        <a href="{{ route('lang.switch', 'gu') }}" lang="gu" role="menuitemradio" aria-checked="{{ $gu ? 'true' : 'false' }}" class="fx-menu-item {{ $gu ? 'is-active' : '' }}"><span class="fx-lang-chip">ગુ</span>ગુજરાતી<i class="ph-bold ph-check fx-check"></i></a>
                    </div>
                </div>
                <div class="fx-dropdown">
                    <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-label="{{ __('messages.theme') }}" class="fx-topbar-link">
                        <i data-theme-icon class="ph ph-desktop text-lg"></i>
                        <span class="hidden lg:inline">{{ __('messages.theme') }}</span>
                    </button>
                    <div class="fx-dropdown-menu !min-w-[11rem]" role="menu">
                        <button type="button" class="fx-menu-item" data-theme-set="light"><i class="ph ph-sun"></i>{{ __('messages.light') }}<i class="ph-bold ph-check fx-check"></i></button>
                        <button type="button" class="fx-menu-item" data-theme-set="dark"><i class="ph ph-moon-stars"></i>{{ __('messages.dark') }}<i class="ph-bold ph-check fx-check"></i></button>
                        <button type="button" class="fx-menu-item" data-theme-set="system"><i class="ph ph-desktop"></i>{{ __('messages.system') }}<i class="ph-bold ph-check fx-check"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== Header ===================== -->
    <header id="fxHeader" class="fx-site-chrome fx-header sticky top-0 z-50 transition-shadow">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 sm:gap-3 xl:gap-5 h-16 lg:h-[72px]">
                <!-- Menu (mobile/tablet) -->
                <button type="button" data-open="mobileMenu" class="fx-icon-btn lg:hidden -ml-1" aria-label="{{ __('messages.menu') }}">
                    <i class="ph ph-list text-2xl"></i>
                </button>

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0" aria-label="FreshExpress">
                    <span class="w-9 h-9 lg:w-10 lg:h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xl shadow-lg shadow-brand-600/25">
                        <i class="ph-fill ph-basket"></i>
                    </span>
                    <span class="leading-none">
                        <span class="block font-extrabold text-[17px] lg:text-lg tracking-tight text-slate-900 dark:text-white">Fresh<span class="text-brand-600 dark:text-brand-400">Express</span></span>
                        <span class="hidden xs:block text-[10px] uppercase font-bold tracking-[.14em] text-slate-400 mt-1">{{ $gu ? 'તાજું · ઝડપી' : 'Grocery · Fast' }}</span>
                    </span>
                </a>

                <!-- Delivery slot pill (desktop) -->
                <div class="hidden xl:flex items-center gap-2.5 pl-4 border-l border-slate-200 dark:border-slate-800 shrink-0" title="{{ $gu ? $navSlot['slot_gu'] : $navSlot['slot_en'] }}">
                    <span class="w-9 h-9 rounded-xl {{ $navSlot['type'] === 'two_hours' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400' }} flex items-center justify-center">
                        <i class="ph-fill {{ $navSlot['type'] === 'two_hours' ? 'ph-lightning' : 'ph-calendar-check' }} text-lg"></i>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.delivery') }}</span>
                        <span class="block text-[13px] font-extrabold text-slate-900 dark:text-white whitespace-nowrap">{{ $navSlotShort }}</span>
                    </span>
                </div>

                <!-- Search (md+) -->
                <form action="{{ route('products.index') }}" method="GET" role="search" class="hidden md:block flex-1 min-w-0">
                    <label class="relative block">
                        <span class="sr-only">{{ __('messages.search_placeholder') }}</span>
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder') }}" title="{{ __('messages.search_placeholder') }}" autocomplete="off"
                               class="fx-search-input w-full h-11 pl-11 pr-14 rounded-2xl text-[13.5px] bg-slate-100 dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 outline-none text-slate-900 dark:text-white placeholder:text-slate-400 transition">
                        <button class="absolute right-1.5 top-1.5 bottom-1.5 w-10 rounded-xl bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center transition-colors" aria-label="{{ __('messages.search') }}" title="{{ __('messages.search') }}">
                            <i class="ph-bold ph-magnifying-glass text-[15px]"></i>
                        </button>
                    </label>
                </form>

                <div class="flex-1 md:hidden"></div>

                <!-- Actions -->
                <div class="flex items-center gap-0.5 sm:gap-1.5 shrink-0">
                    <a href="{{ route('wishlist.index') }}" class="fx-icon-btn hidden sm:inline-flex" title="{{ __('messages.wishlist') }}" aria-label="{{ __('messages.wishlist') }}">
                        <i class="ph ph-heart text-[22px]"></i>
                        <span id="globalWishlistBadge" data-wishlist-count data-count="{{ $wishCount }}" class="fx-count-badge">{{ $wishCount }}</span>
                    </a>

                    <div class="fx-dropdown hidden lg:block">
                        <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" class="fx-account-btn">
                            @auth
                                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-sm shrink-0">{{ $userInitial }}</span>
                            @else
                                <span class="fx-ico-tile w-9 h-9 rounded-xl text-lg shrink-0"><i class="ph ph-user"></i></span>
                            @endauth
                            <span class="text-left leading-tight min-w-0">
                                <span class="block text-[10px] font-semibold text-slate-400">{{ __('messages.hello') }},</span>
                                <span class="block text-[13px] font-bold text-slate-900 dark:text-white max-w-[6.5rem] truncate">{{ Auth::check() ? $firstName : __('messages.sign_in') }}</span>
                            </span>
                            <i class="ph-bold ph-caret-down text-xs text-slate-400 fx-caret"></i>
                        </button>
                        <div class="fx-dropdown-menu !w-64" role="menu">
                            @auth
                                <div class="flex items-center gap-3 px-3 py-2.5 mb-1 border-b border-slate-100 dark:border-slate-800">
                                    <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex items-center justify-center font-extrabold shrink-0">{{ $userInitial }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name ?: __('messages.my_account') }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400 font-mono">+91 {{ Auth::user()->phone }}</span>
                                    </span>
                                </div>
                                <a href="{{ route('customer.profile') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-user-circle"></i>{{ __('messages.my_profile') }}</a>
                                <a href="{{ route('orders.index') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-package"></i>{{ __('messages.my_orders') }}</a>
                                <a href="{{ route('addresses.index') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-map-pin"></i>{{ __('messages.addresses') }}</a>
                                <a href="{{ route('wishlist.index') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-heart"></i>{{ __('messages.wishlist') }}<span data-wishlist-count data-count="{{ $wishCount }}" class="fx-menu-count">{{ $wishCount }}</span></a>
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-gauge"></i>{{ __('messages.admin_dashboard') }}</a>
                                @endif
                                <form action="{{ route('customer.logout') }}" method="POST" class="mt-1 pt-1 border-t border-slate-100 dark:border-slate-800">
                                    @csrf
                                    <button class="fx-menu-item fx-menu-danger" role="menuitem"><i class="ph ph-sign-out"></i>{{ __('messages.logout') }}</button>
                                </form>
                            @else
                                <div class="px-3 pt-2 pb-3">
                                    <p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ __('messages.guest_welcome') }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('messages.guest_welcome_sub') }}</p>
                                    <a href="{{ route('customer.login') }}" class="fx-btn fx-btn-primary w-full mt-3"><i class="ph ph-sign-in"></i>{{ __('messages.sign_in') }}</a>
                                    <p class="text-[12px] text-slate-500 dark:text-slate-400 mt-2.5 text-center">{{ __('messages.new_customer') }} <a href="{{ $registerUrl }}" class="font-bold text-brand-700 dark:text-brand-400 hover:underline">{{ __('messages.register') }}</a></p>
                                </div>
                                <div class="pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <a href="{{ route('order.track') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-package"></i>{{ __('messages.track_order') }}</a>
                                    <a href="{{ route('wishlist.index') }}" class="fx-menu-item" role="menuitem"><i class="ph ph-heart"></i>{{ __('messages.wishlist') }}</a>
                                </div>
                            @endauth
                        </div>
                    </div>

                    <button type="button" onclick="openCartDrawer()" class="fx-cart-btn relative inline-flex items-center gap-2 h-10 pl-2.5 pr-2.5 sm:pr-3.5 rounded-xl sm:bg-brand-600 sm:hover:bg-brand-700 sm:text-white text-slate-700 dark:text-slate-200 sm:dark:text-white transition-colors" aria-label="{{ __('messages.cart') }}">
                        <span class="relative">
                            <i class="ph ph-shopping-cart-simple text-[22px]"></i>
                            <span id="globalCartBadge" data-cart-count data-count="{{ $cartQty }}" class="fx-count-badge sm:!bg-amber-400 sm:!text-slate-900 sm:!shadow-none">{{ $cartQty }}</span>
                        </span>
                        <span class="hidden sm:inline text-[13px] font-bold">{{ __('messages.cart') }}</span>
                    </button>
                </div>
            </div>

            <!-- Search (mobile) -->
            <form action="{{ route('products.index') }}" method="GET" role="search" class="md:hidden pb-3 -mt-0.5">
                <label class="relative block">
                    <span class="sr-only">{{ __('messages.search_placeholder') }}</span>
                    <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder_short') }}" autocomplete="off"
                           class="w-full h-11 pl-10 pr-4 rounded-2xl text-sm bg-slate-100 dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 outline-none text-slate-900 dark:text-white placeholder:text-slate-400">
                </label>
            </form>
        </div>

        <!-- Main navigation (desktop) -->
        <nav class="hidden lg:block border-t border-slate-100 dark:border-slate-800/80 bg-white/70 dark:bg-slate-900/70 backdrop-blur" aria-label="{{ __('messages.menu') }}">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between gap-3 text-[13px] font-bold">
                <!-- Left & Center Navigation -->
                <div class="flex items-center gap-1 sm:gap-2 min-w-0 overflow-x-auto no-scrollbar">
                    <!-- All categories (mega menu dropdown) -->
                    <div class="fx-dropdown shrink-0">
                        <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" class="fx-allcat-btn flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 dark:bg-brand-500/10 dark:hover:bg-brand-500/20 text-brand-700 dark:text-brand-300 font-extrabold whitespace-nowrap transition-colors">
                            <i class="ph-bold ph-squares-four text-base"></i><span>{{ __('messages.all_categories') }}</span><i class="ph-bold ph-caret-down text-xs opacity-70 fx-caret"></i>
                        </button>
                        <div class="fx-dropdown-menu fx-mega !left-0 !right-auto !p-0" role="menu">
                            <div class="flex items-center justify-between px-5 pt-4 pb-2">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.shop_by_category') }}</p>
                                <a href="{{ route('categories.index') }}" class="text-[12px] font-bold text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1">{{ __('messages.browse_all_categories') }}<i class="ph-bold ph-arrow-right"></i></a>
                            </div>
                            <div class="grid grid-cols-3 gap-1 px-3 pb-3">
                                @foreach($navCategories as $navCat)
                                    <div class="fx-mega-col">
                                        <a href="{{ route('categories.show', $navCat->slug) }}" class="fx-mega-head group" role="menuitem">
                                            <span class="fx-ico-tile w-10 h-10 rounded-xl text-[15px] shrink-0"><i class="{{ $navCat->icon ?: 'fa-solid fa-layer-group' }}"></i></span>
                                            <span class="min-w-0">
                                                <span class="block text-[13px] font-bold text-slate-800 dark:text-slate-100 group-hover:text-brand-700 dark:group-hover:text-brand-400 leading-snug">{{ $navCat->localized_name }}</span>
                                                <span class="block text-[11px] text-slate-400 font-medium">{{ __('messages.subcategories_count', ['count' => $navCat->subCategories->count()]) }}</span>
                                            </span>
                                        </a>
                                        @if($navCat->subCategories->count())
                                            <ul class="mt-1 ml-[3.25rem] space-y-0.5">
                                                @foreach($navCat->subCategories->take(5) as $navSub)
                                                    <li><a href="{{ route('subcategories.show', $navSub->slug) }}" class="fx-mega-sub" role="menuitem">{{ $navSub->localized_name }}</a></li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 rounded-b-2xl text-[12px]">
                                <span class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400 font-medium"><i class="ph ph-clock text-base"></i>{{ __('messages.order_cutoff_note') }}</span>
                                <a href="{{ route('pages.offers') }}" class="inline-flex items-center gap-1.5 font-bold text-amber-700 dark:text-amber-400 hover:underline"><i class="ph-fill ph-seal-percent"></i>{{ __('messages.offers') }}</a>
                            </div>
                        </div>
                    </div>

                    <span class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-1 shrink-0" aria-hidden="true"></span>

                    <!-- Clean Structured Nav Menu Links -->
                    <div class="flex items-center gap-0.5 sm:gap-1">
                        <a href="{{ route('home') }}" class="fx-nav-link flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors {{ request()->routeIs('home') ? '!text-brand-600 dark:!text-brand-400 !bg-brand-50 dark:!bg-brand-500/10' : '' }}">
                            <i class="ph ph-house text-base"></i><span>{{ __('messages.home') }}</span>
                        </a>

                        <a href="{{ route('products.index') }}" class="fx-nav-link flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors {{ request()->routeIs('products.index') && !request('category') ? '!text-brand-600 dark:!text-brand-400 !bg-brand-50 dark:!bg-brand-500/10' : '' }}">
                            <i class="ph ph-storefront text-base"></i><span>{{ __('messages.all_products') }}</span>
                        </a>

                        <a href="{{ route('categories.index') }}" class="fx-nav-link flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors {{ request()->routeIs('categories.*', 'subcategories.*') ? '!text-brand-600 dark:!text-brand-400 !bg-brand-50 dark:!bg-brand-500/10' : '' }}">
                            <i class="ph ph-squares-four text-base"></i><span>{{ __('messages.categories') }}</span>
                        </a>

                        <a href="{{ route('pages.offers') }}" class="fx-nav-offers flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-extrabold whitespace-nowrap transition-colors {{ request()->routeIs('pages.offers') ? 'ring-2 ring-amber-400' : '' }}">
                            <i class="ph-fill ph-seal-percent text-base"></i><span>{{ __('messages.offers_and_deals') }}</span>
                        </a>

                        @if($activePageSlugs->contains('about-us'))
                            <a href="{{ route('pages.show', 'about-us') }}" class="fx-nav-link flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors {{ request()->is('page/about-us*') ? '!text-brand-600 dark:!text-brand-400 !bg-brand-50 dark:!bg-brand-500/10' : '' }}">
                                <i class="ph ph-info text-base"></i><span>{{ __('messages.about_us') }}</span>
                            </a>
                        @endif

                        <a href="{{ route('order.track') }}" class="fx-nav-link flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors {{ request()->routeIs('order.track') ? '!text-brand-600 dark:!text-brand-400 !bg-brand-50 dark:!bg-brand-500/10' : '' }}">
                            <i class="ph ph-package text-base"></i><span>{{ __('messages.track_order') }}</span>
                        </a>

                        <a href="#fxSupport" class="fx-nav-link flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors">
                            <i class="ph ph-headset text-base"></i><span>{{ __('messages.contact_help') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Right Side Express Badge & User Orders -->
                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-xs text-brand-700 dark:text-brand-300 font-extrabold bg-brand-50 dark:bg-brand-900/40 border border-brand-200 dark:border-brand-700/50 px-3 py-1 rounded-full shadow-xs whitespace-nowrap">
                        <i class="ph-fill ph-lightning text-amber-500 text-sm"></i>{{ $gu ? '૨ કલાક સુપરફાસ્ટ ડિલિવરી' : '2-Hour Express Delivery' }}
                    </span>
                </div>
            </div>
        </nav>
    </header>

    <!-- ===================== Main ===================== -->
    <main id="main" class="flex-1 w-full">
        @if(session('warning') || session('error') || $errors->any())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-2">
                @foreach(['warning' => ['amber', 'ph-warning'], 'error' => ['rose', 'ph-warning-circle']] as $key => [$tone, $icon])
                    @if(session($key))
                        <div role="alert" class="flex items-start gap-3 p-3.5 rounded-2xl text-sm font-semibold {{ $tone === 'amber' ? 'bg-amber-50 text-amber-900 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/30' : 'bg-rose-50 text-rose-900 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:border-rose-500/30' }}">
                            <i class="ph-fill {{ $icon }} text-lg shrink-0 {{ $tone === 'amber' ? 'text-amber-500' : 'text-rose-500' }}"></i>
                            <span class="flex-1">{{ is_string(session($key)) ? __(session($key)) : session($key) }}</span>
                            <button type="button" onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100" aria-label="{{ __('messages.close') }}"><i class="ph ph-x"></i></button>
                        </div>
                    @endif
                @endforeach
                @if($errors->any())
                    <div role="alert" class="flex items-start gap-3 p-3.5 rounded-2xl text-sm bg-rose-50 text-rose-900 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:border-rose-500/30">
                        <i class="ph-fill ph-warning-circle text-lg shrink-0 text-rose-500"></i>
                        <ul class="flex-1 space-y-0.5 font-semibold">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                        <button type="button" onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100" aria-label="{{ __('messages.close') }}"><i class="ph ph-x"></i></button>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <!-- ===================== Footer ===================== -->
    <footer class="fx-site-chrome mt-16 bg-slate-900 dark:bg-slate-950 text-slate-400 border-t border-slate-800">
        <!-- Trust strip -->
        <div class="border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach([
                    ['ph-plant', $gu ? '૧૦૦% ખેતરથી તાજું' : '100% farm fresh', $gu ? 'વિશ્વસનીય ખેડૂતો પાસેથી' : 'Sourced from trusted farmers'],
                    ['ph-lightning', $gu ? '૨ કલાક ડિલિવરી' : '2-hour delivery', $gu ? 'બપોરે ૧૨ પહેલાંના ઓર્ડર' : 'On orders before 12 PM'],
                    ['ph-shield-check', $gu ? 'સુરક્ષિત ચુકવણી' : 'Safe payments', 'COD · UPI'],
                    ['ph-arrow-counter-clockwise', $gu ? 'સરળ રિટર્ન' : 'Easy returns', $gu ? 'દરવાજે જ બદલી' : 'Doorstep replacement'],
                ] as [$ic, $h, $s])
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-slate-800 text-brand-400 flex items-center justify-center text-xl shrink-0"><i class="ph-duotone {{ $ic }}"></i></span>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-bold text-white">{{ $h }}</span>
                            <span class="block text-[11px] text-slate-400 truncate">{{ $s }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-x-8 gap-y-2 md:gap-y-8">
                <div class="md:col-span-12 lg:col-span-4 space-y-4 pb-6 md:pb-0">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xl"><i class="ph-fill ph-basket"></i></span>
                        <span>
                            <span class="block font-extrabold text-lg text-white tracking-tight">FreshExpress</span>
                            <span class="block text-[11px] text-brand-400 font-bold">ફ્રેશ એક્સપ્રેસ સ્ટોર</span>
                        </span>
                    </a>
                    <p class="text-[13px] leading-relaxed max-w-sm">
                        {{ $gu
                            ? 'ખેતરમાંથી સીધા તાજા શાકભાજી, ફળો, દેશી ડેરી અને સ્વાદિષ્ટ ગુજરાતી નાસ્તો તમારા ઘર સુધી. બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં ડિલિવરી મેળવો.'
                            : 'Farm-fresh fruits, vegetables, pure dairy, authentic snacks and groceries delivered to your door — in 2 hours for orders placed before 12 PM.' }}
                    </p>
                    <div class="flex items-center gap-2">
                        @foreach(['ph-whatsapp-logo' => 'WhatsApp', 'ph-instagram-logo' => 'Instagram', 'ph-facebook-logo' => 'Facebook', 'ph-youtube-logo' => 'YouTube'] as $sic => $sname)
                            <span title="{{ $sname }}" class="fx-social"><i class="ph {{ $sic }} text-lg"></i></span>
                        @endforeach
                    </div>
                </div>

                @php
                    $footCols = [
                        [__('messages.quick_links'), [
                            [route('products.index'), __('messages.products')],
                            [route('categories.index'), __('messages.all_categories')],
                            [route('pages.offers'), __('messages.offers')],
                            [route('order.track'), __('messages.track_order')],
                        ]],
                        [$gu ? 'મારું એકાઉન્ટ' : 'Your account', [
                            [route('customer.profile'), __('messages.my_profile')],
                            [route('orders.index'), __('messages.my_orders')],
                            [route('addresses.index'), __('messages.addresses')],
                            [route('wishlist.index'), __('messages.wishlist')],
                        ]],
                        [$gu ? 'માહિતી' : 'Information', [
                            ...collect([['about-us', __('messages.about_us')], ['legal-information', __('messages.legal_info')], ['privacy-policy', __('messages.privacy_policy')]])
                                ->filter(fn($l) => $activePageSlugs->contains($l[0]))->map(fn($l) => [route('pages.show', $l[0]), $l[1]])->values()->all(),
                            [route('admin.login'), $gu ? 'એડમિન પોર્ટલ' : 'Admin portal'],
                        ]],
                    ];
                @endphp
                @foreach($footCols as [$colTitle, $links])
                    <div class="fx-acc md:col-span-4 lg:col-span-2 border-t border-slate-800 md:border-0">
                        <button type="button" class="fx-acc-toggle w-full flex items-center justify-between py-3.5 md:py-0 md:mb-4 text-left" aria-expanded="false">
                            <span class="text-white font-bold text-[13px] uppercase tracking-wider">{{ $colTitle }}</span>
                            <i class="ph-bold ph-caret-down fx-acc-caret text-slate-500 transition-transform"></i>
                        </button>
                        <ul class="fx-acc-body space-y-2.5 text-[13px] pb-4 md:pb-0">
                            @foreach($links as [$href, $label])
                                <li><a href="{{ $href }}" class="fx-foot-link">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div id="fxSupport" class="fx-acc md:col-span-12 lg:col-span-2 border-y border-slate-800 md:border-0 scroll-mt-40">
                    <button type="button" class="fx-acc-toggle w-full flex items-center justify-between py-3.5 md:py-0 md:mb-4 text-left" aria-expanded="false">
                        <span class="text-white font-bold text-[13px] uppercase tracking-wider">{{ __('messages.customer_support') }}</span>
                        <i class="ph-bold ph-caret-down fx-acc-caret text-slate-500 transition-transform"></i>
                    </button>
                    <div class="fx-acc-body space-y-2 text-[13px] pb-4 md:pb-0">
                        <a href="tel:+919876543210" class="fx-foot-link flex items-center gap-2 !text-white font-bold"><i class="ph ph-phone"></i>+91 98765 43210</a>
                        <a href="mailto:support@freshexpress.in" class="fx-foot-link flex items-center gap-2"><i class="ph ph-envelope-simple"></i>support@freshexpress.in</a>
                        <p class="flex items-start gap-2 text-slate-500"><i class="ph ph-clock mt-0.5"></i><span>7:00 AM – 10:00 PM<br>{{ $gu ? 'અમદાવાદ અને ગુજરાત મેટ્રો' : 'Ahmedabad & Gujarat metro' }}</span></p>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12px] text-slate-500">
                <p>&copy; {{ date('Y') }} FreshExpress Grocery. {{ $gu ? 'સર્વાધિકાર સુરક્ષિત.' : 'All rights reserved.' }}</p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <span class="px-2 py-1 rounded-md bg-slate-800 text-slate-300 font-semibold">FSSAI 10721026000123</span>
                    <span class="px-2 py-1 rounded-md bg-slate-800 text-slate-300 font-semibold inline-flex items-center gap-1"><i class="ph ph-money"></i>COD</span>
                    <span class="px-2 py-1 rounded-md bg-slate-800 text-slate-300 font-semibold inline-flex items-center gap-1"><i class="ph ph-qr-code"></i>UPI</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================== Mobile tab bar ===================== -->
    <nav class="fx-site-chrome fx-tabbar lg:hidden" aria-label="{{ $gu ? 'મુખ્ય નેવિગેશન' : 'Primary' }}">
        <div class="flex max-w-xl mx-auto">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}"><i class="ph{{ request()->routeIs('home') ? '-fill' : '' }} ph-house"></i><span>{{ __('messages.home') }}</span></a>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*', 'subcategories.*', 'products.*') ? 'is-active' : '' }}"><i class="ph{{ request()->routeIs('categories.*', 'subcategories.*', 'products.*') ? '-fill' : '' }} ph-squares-four"></i><span>{{ __('messages.categories') }}</span></a>
            <a href="{{ route('pages.offers') }}" class="{{ request()->routeIs('pages.offers') ? 'is-active' : '' }}"><i class="ph{{ request()->routeIs('pages.offers') ? '-fill' : '' }} ph-seal-percent"></i><span>{{ $gu ? 'ઓફર્સ' : 'Offers' }}</span></a>
            <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*', 'checkout.*') ? 'is-active' : '' }}">
                <span class="relative"><i class="ph{{ request()->routeIs('cart.*', 'checkout.*') ? '-fill' : '' }} ph-shopping-cart-simple"></i></span>
                <span data-cart-count data-count="{{ $cartQty }}" class="fx-count-badge is-brand">{{ $cartQty }}</span>
                <span>{{ __('messages.cart') }}</span>
            </a>
            @php $acctActive = request()->routeIs('customer.profile', 'profile.*', 'orders.*', 'order.*', 'addresses.*', 'wishlist.*', 'login', 'customer.login', 'customer.verify.view', 'auth.verify.view', 'register', 'customer.register'); @endphp
            {{-- "Account" opens the full menu (account, orders, wishlist, help, language…) so every menu is one tap away --}}
            <button type="button" data-open="mobileMenu" class="{{ $acctActive ? 'is-active' : '' }}" aria-label="{{ __('messages.menu') }}">
                @auth
                    <span class="fx-tab-avatar">{{ $userInitial }}</span>
                @else
                    <i class="ph{{ $acctActive ? '-fill' : '' }} ph-user-circle"></i>
                @endauth
                <span>{{ __('messages.account') }}</span>
            </button>
        </div>
    </nav>

    <!-- ===================== Off-canvas menu ===================== -->
    <div id="mobileMenu" class="fx-overlay" aria-hidden="true">
        <div class="fx-backdrop" data-close="mobileMenu"></div>
        <aside class="fx-panel fx-panel-left outline-none" tabindex="-1" role="dialog" aria-modal="true" aria-label="{{ __('messages.menu') }}">
            <div class="flex items-center justify-between px-4 h-16 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <span class="inline-flex items-center gap-2 font-extrabold text-slate-900 dark:text-white"><span class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center"><i class="ph-fill ph-basket"></i></span>Fresh<span class="text-brand-600 dark:text-brand-400 -ml-2">Express</span></span>
                <button type="button" class="fx-icon-btn" data-close="mobileMenu" aria-label="{{ __('messages.close') }}"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-5">
                @auth
                    <a href="{{ route('customer.profile') }}" class="flex items-center gap-3 p-3 rounded-2xl bg-brand-50 dark:bg-brand-500/10 fx-ihover">
                        <span class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex items-center justify-center font-extrabold">{{ $userInitial }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.hello') }},</span>
                            <span class="block font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name ?: __('messages.my_account') }}</span>
                        </span>
                        <i class="ph-bold ph-caret-right"></i>
                    </a>
                @else
                    <div class="p-4 rounded-2xl bg-brand-50 dark:bg-brand-500/10">
                        <p class="font-extrabold text-slate-900 dark:text-white text-[15px]">{{ __('messages.guest_welcome') }}</p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">{{ __('messages.guest_welcome_sub') }}</p>
                        <div class="grid grid-cols-2 gap-2 mt-3">
                            <a href="{{ route('customer.login') }}" class="fx-btn fx-btn-primary"><i class="ph ph-sign-in"></i>{{ __('messages.sign_in') }}</a>
                            <a href="{{ $registerUrl }}" class="fx-btn fx-btn-outline"><i class="ph ph-user-plus"></i>{{ __('messages.register') }}</a>
                        </div>
                    </div>
                @endauth

                <div class="space-y-0.5">
                    @php
                        $mmLinks = [
                            [route('home'), 'ph-house', __('messages.home'), request()->routeIs('home')],
                            [route('products.index'), 'ph-storefront', __('messages.all_products'), request()->routeIs('products.index') && !request('category')],
                            [route('categories.index'), 'ph-squares-four', __('messages.categories'), request()->routeIs('categories.index')],
                            [route('pages.offers'), 'ph-seal-percent', __('messages.offers_and_deals'), request()->routeIs('pages.offers')],
                        ];
                        if ($activePageSlugs->contains('about-us')) {
                            $mmLinks[] = [route('pages.show', 'about-us'), 'ph-info', __('messages.about_us'), request()->is('page/about-us*')];
                        }
                        $mmLinks[] = [route('order.track'), 'ph-package', __('messages.track_order'), request()->routeIs('order.track')];
                        $mmLinks[] = ['#fxSupport', 'ph-headset', __('messages.contact_help'), false];
                    @endphp
                    @foreach($mmLinks as [$href, $ic, $label, $on])
                        <a href="{{ $href }}" @if(str_starts_with($href, '#')) data-close="mobileMenu" data-scroll-to="{{ $href }}" @endif class="fx-menu-item !py-2.5 {{ $on ? 'is-current' : '' }}"><i class="ph {{ $ic }}"></i>{{ $label }}</a>
                    @endforeach
                    @auth
                        <a href="{{ route('orders.index') }}" class="fx-menu-item !py-2.5 {{ request()->routeIs('orders.*', 'order.show') ? 'is-current' : '' }}"><i class="ph ph-receipt"></i>{{ __('messages.my_orders') }}</a>
                        <a href="{{ route('addresses.index') }}" class="fx-menu-item !py-2.5 {{ request()->routeIs('addresses.*') ? 'is-current' : '' }}"><i class="ph ph-map-pin"></i>{{ __('messages.addresses') }}</a>
                    @endauth
                    <a href="{{ route('wishlist.index') }}" class="fx-menu-item !py-2.5 {{ request()->routeIs('wishlist.*') ? 'is-current' : '' }}"><i class="ph ph-heart"></i>{{ __('messages.wishlist') }}<span data-wishlist-count data-count="{{ $wishCount }}" class="fx-menu-count">{{ $wishCount }}</span></a>
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="fx-menu-item !py-2.5"><i class="ph ph-gauge"></i>{{ __('messages.admin_dashboard') }}</a>
                        @endif
                    @endauth
                </div>

                <div>
                    <p class="px-1 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.categories') }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($navCategories as $navCat)
                            <a href="{{ route('categories.show', $navCat->slug) }}" class="group flex items-center gap-2 p-2 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                                <span class="fx-ico-tile w-8 h-8 rounded-lg text-sm shrink-0"><i class="{{ $navCat->icon ?: 'fa-solid fa-layer-group' }}"></i></span>
                                <span class="text-[12px] font-semibold text-slate-700 dark:text-slate-200 leading-tight line-clamp-2">{{ $navCat->localized_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-0.5">
                    <p class="px-1 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.explore') }}</p>
                    @foreach($infoLinks as [$infoHref, $infoIcon, $infoLabel])
                        <a href="{{ $infoHref }}" @if(str_starts_with($infoHref, '#')) data-close="mobileMenu" data-scroll-to="{{ $infoHref }}" @endif class="fx-menu-item !py-2.5"><i class="ph {{ $infoIcon }}"></i>{{ $infoLabel }}</a>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <p class="px-1 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.language') }}</p>
                        <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-800">
                            <a href="{{ route('lang.switch', 'en') }}" lang="en" aria-current="{{ $gu ? 'false' : 'true' }}" class="inline-flex items-center justify-center gap-1.5 py-2 rounded-lg text-[13px] font-bold {{ !$gu ? 'bg-white dark:bg-slate-950 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500' }}">@if(!$gu)<i class="ph-bold ph-check text-brand-600"></i>@endif English</a>
                            <a href="{{ route('lang.switch', 'gu') }}" lang="gu" aria-current="{{ $gu ? 'true' : 'false' }}" class="inline-flex items-center justify-center gap-1.5 py-2 rounded-lg text-[13px] font-bold {{ $gu ? 'bg-white dark:bg-slate-950 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500' }}">@if($gu)<i class="ph-bold ph-check text-brand-600"></i>@endif ગુજરાતી</a>
                        </div>
                    </div>
                    <div>
                        <p class="px-1 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.theme') }}</p>
                        <div class="grid grid-cols-3 gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-800 fx-seg">
                            <button type="button" data-theme-set="light" class="py-2 rounded-lg text-[12px] font-bold text-slate-500 inline-flex items-center justify-center gap-1"><i class="ph ph-sun"></i>{{ __('messages.light') }}</button>
                            <button type="button" data-theme-set="dark" class="py-2 rounded-lg text-[12px] font-bold text-slate-500 inline-flex items-center justify-center gap-1"><i class="ph ph-moon-stars"></i>{{ __('messages.dark') }}</button>
                            <button type="button" data-theme-set="system" class="py-2 rounded-lg text-[12px] font-bold text-slate-500 inline-flex items-center justify-center gap-1"><i class="ph ph-desktop"></i>{{ __('messages.system') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            @auth
                <form action="{{ route('customer.logout') }}" method="POST" class="p-4 border-t border-slate-100 dark:border-slate-800 shrink-0" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom))">
                    @csrf
                    <button class="fx-btn fx-btn-danger w-full"><i class="ph ph-sign-out"></i>{{ __('messages.logout') }}</button>
                </form>
            @endauth
        </aside>
    </div>
    <!-- ===================== Cart drawer ===================== -->
    <div id="cartDrawer" class="fx-overlay" aria-hidden="true">
        <div class="fx-backdrop" onclick="closeCartDrawer()"></div>
        <aside class="fx-panel fx-panel-right outline-none" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="cartDrawerTitle">
            <div class="flex items-center justify-between px-5 h-16 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-lg"><i class="ph-fill ph-shopping-cart-simple"></i></span>
                    <div>
                        <h3 id="cartDrawerTitle" class="font-extrabold text-slate-900 dark:text-white text-[15px] leading-tight">{{ __('messages.shopping_cart') }}</h3>
                        <p id="drawerItemCount" class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">{{ $cartQty }} {{ $gu ? 'વસ્તુઓ' : 'items' }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeCartDrawer()" class="fx-icon-btn" aria-label="{{ __('messages.close') }}"><i class="ph ph-x text-xl"></i></button>
            </div>

            <div class="px-5 py-2.5 bg-amber-50 dark:bg-amber-500/10 border-b border-amber-100 dark:border-amber-500/20 flex items-center gap-2 text-[12px] text-amber-900 dark:text-amber-200 font-semibold shrink-0">
                <i class="ph-fill ph-lightning text-amber-500"></i>
                <span id="drawerDeliverySlot" class="truncate">{{ $gu ? $navSlot['slot_gu'] : $navSlot['slot_en'] }}</span>
            </div>

            <div id="drawerMeter" class="px-5 pt-4 shrink-0 hidden">
                <p id="drawerFreeText" class="text-[12px] text-slate-600 dark:text-slate-300"></p>
                <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden"><div id="drawerFreeBar" class="h-full rounded-full bg-gradient-to-r from-brand-500 to-teal-400 transition-all duration-500" style="width:0"></div></div>
            </div>

            <div id="drawerItemsList" class="flex-1 overflow-y-auto px-2 py-3 space-y-1"></div>

            <div id="drawerFooter" class="hidden p-5 border-t border-slate-100 dark:border-slate-800 space-y-3 shrink-0" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom))">
                <div class="flex justify-between items-baseline">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('messages.item_total') }}</span>
                    <span id="drawerSubtotal" class="text-lg font-extrabold text-slate-900 dark:text-white">₹0.00</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-1">{{ $gu ? 'ડિલિવરી અને ઑફર ચેકઆઉટ પર લાગુ થશે.' : 'Delivery fee and coupons are applied at checkout.' }}</p>
                <div class="grid grid-cols-5 gap-2 pt-1">
                    <a href="{{ route('cart.index') }}" class="col-span-2 fx-btn fx-btn-outline">{{ $gu ? 'કાર્ટ જુઓ' : 'View cart' }}</a>
                    <a href="{{ route('checkout.index') }}" class="col-span-3 fx-btn fx-btn-primary">{{ __('messages.proceed_to_checkout') }}<i class="ph-bold ph-arrow-right"></i></a>
                </div>
            </div>
        </aside>
    </div>

    <!-- ===================== Quick view ===================== -->
    <div id="quickViewModal" class="fx-overlay" aria-hidden="true">
        <div class="fx-backdrop" onclick="closeQuickView()"></div>
        <div class="fx-panel fx-panel-center fx-sheet-mobile outline-none" style="--fx-modal-w: 760px" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="qvTitle">
            <button type="button" onclick="closeQuickView()" class="fx-icon-btn absolute top-3 right-3 z-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur" aria-label="{{ __('messages.close') }}"><i class="ph ph-x text-xl"></i></button>
            <div class="overflow-y-auto">
                <div id="qvSkeleton" class="grid sm:grid-cols-2 gap-6 p-5 sm:p-7">
                    <div class="fx-skeleton aspect-square rounded-2xl"></div>
                    <div class="space-y-3 pt-2"><div class="fx-skeleton h-4 w-24"></div><div class="fx-skeleton h-7 w-3/4"></div><div class="fx-skeleton h-4 w-20"></div><div class="fx-skeleton h-8 w-32"></div><div class="fx-skeleton h-16 w-full"></div><div class="fx-skeleton h-11 w-full"></div></div>
                </div>
                <div id="qvBody" class="hidden grid sm:grid-cols-2 gap-6 p-5 sm:p-7">
                    <div class="aspect-square rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800">
                        <img id="qvImage" src="data:image/gif;base64,R0lGODlhAQABAAAAACw=" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex flex-col">
                        <span id="qvCategory" class="self-start px-2.5 py-1 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300 font-bold text-[11px] uppercase tracking-wider"></span>
                        <h3 id="qvTitle" class="mt-3 text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white leading-tight"></h3>
                        <p id="qvUnit" class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-semibold"></p>
                        <div class="flex items-baseline flex-wrap gap-2 mt-4">
                            <span id="qvPrice" class="text-2xl font-extrabold text-slate-900 dark:text-white"></span>
                            <span id="qvMrp" class="text-sm text-slate-400 line-through"></span>
                            <span id="qvDiscount" class="hidden px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 text-xs font-extrabold"></span>
                        </div>
                        <p id="qvDescription" class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-4"></p>
                        <div class="mt-auto pt-5 flex items-center gap-3">
                            <div class="flex items-center h-11 rounded-xl border border-slate-200 dark:border-slate-700">
                                <button type="button" data-qv-step="-1" class="w-10 h-full text-slate-600 dark:text-slate-300" aria-label="-"><i class="ph-bold ph-minus"></i></button>
                                <input type="number" id="qvQty" value="1" min="1" class="fx-noarrows w-10 h-full bg-transparent text-center font-bold text-sm text-slate-900 dark:text-white outline-none" aria-label="{{ __('messages.quantity') }}">
                                <button type="button" data-qv-step="1" class="w-10 h-full text-slate-600 dark:text-slate-300" aria-label="+"><i class="ph-bold ph-plus"></i></button>
                            </div>
                            <button type="button" id="qvAddToCartBtn" class="fx-btn fx-btn-primary flex-1 h-11"><i class="ph-bold ph-shopping-cart-simple"></i><span>{{ __('messages.add_to_cart') }}</span></button>
                        </div>
                        <a id="qvLink" href="#" class="hidden mt-3 text-center text-[13px] font-bold text-brand-700 dark:text-brand-400 hover:underline">{{ __('messages.view_details') }} &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to top -->
    <button type="button" id="fxBackToTop" class="fixed right-4 z-40 w-11 h-11 rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xl flex items-center justify-center opacity-0 pointer-events-none translate-y-3 transition-all fx-above-tabbar mb-4 lg:mb-6" aria-label="{{ $gu ? 'ઉપર જાઓ' : 'Back to top' }}">
        <i class="ph-bold ph-arrow-up text-lg"></i>
    </button>

    <div id="fxToasts" aria-live="polite"></div>

    <!-- ===================== LIVE CUSTOMIZER WIDGET & DRAWER ===================== -->
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

    <!-- Customizer Drawer Modal -->
    <div id="customizerDrawer" class="customizer-backdrop is-hidden" aria-hidden="true">
        <div class="customizer-panel" role="dialog" aria-modal="true" aria-label="Customizer">
            <!-- Header -->
            <div class="customizer-header">
                <div class="flex items-center gap-2.5 font-black text-slate-800 dark:text-white text-base">
                    <i class="ph-duotone ph-gear-six text-2xl text-emerald-600 dark:text-emerald-400"></i>
                    <span class="text-base font-extrabold tracking-tight">{{ __('messages.customizer') ?? 'Customizer' }}</span>
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
                        <i class="ph ph-sun text-sm"></i> {{ __('messages.appearance') ?? 'APPEARANCE' }}
                    </label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <button type="button" class="customizer-opt-btn" data-cust-theme="light">
                            <i class="ph ph-sun text-xl text-amber-500"></i>
                            <span>{{ __('messages.light') }}</span>
                            <i class="ph-bold ph-check opt-check"></i>
                        </button>
                        <button type="button" class="customizer-opt-btn" data-cust-theme="dark">
                            <i class="ph ph-moon text-xl text-indigo-400"></i>
                            <span>{{ __('messages.dark') }}</span>
                            <i class="ph-bold ph-check opt-check"></i>
                        </button>
                        <button type="button" class="customizer-opt-btn" data-cust-theme="system">
                            <i class="ph ph-desktop text-xl text-slate-400"></i>
                            <span>{{ __('messages.system') ?? 'Auto' }}</span>
                            <i class="ph-bold ph-check opt-check"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. LANGUAGE -->
                <div class="customizer-section">
                    <label class="customizer-label">
                        <i class="ph ph-translate text-sm"></i> {{ __('messages.language') }}
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
                        <i class="ph ph-arrows-out text-sm"></i> {{ __('messages.view_options') ?? 'VIEW OPTIONS' }}
                    </label>
                    <button type="button" id="fullscreenToggleBtn" class="customizer-full-btn">
                        <i class="ph ph-corners-out text-lg"></i>
                        <span id="fullscreenLabel">{{ __('messages.fullscreen') ?? 'Go Full Screen' }}</span>
                    </button>
                </div>
            </div>

            <!-- Footer Tag -->
            <div class="customizer-footer">
                <span>FreshExpress v2.0</span>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        window.FX = {
            locale: @json(app()->getLocale()),
            auth: @json(Auth::check()),
            freeThreshold: 499,
            flash: @json($flash),
            routes: {
                cartAdd: @json(route('cart.add')),
                cartDrawer: @json(route('cart.drawer')),
                cartUpdate: @json(route('cart.update', '__ID__')),
                cartRemove: @json(route('cart.remove', '__ID__')),
                wishlistToggle: @json(route('wishlist.toggle')),
                quickView: @json(route('products.quick_view', '__ID__')),
                products: @json(route('products.index')),
                login: @json(route('customer.login'))
            },
            i18n: @json($fxI18n)
        };
    </script>
    <script src="{{ asset('assets/shared/fx-select.js') }}?v=2.2.0"></script>
    <script src="{{ asset('assets/front/store.js') }}?v=4"></script>
    <script>
        // ==================== STORE LIVE CUSTOMIZER CONTROLLER ====================
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
                var currentTheme = localStorage.getItem('theme') || localStorage.getItem('admin_theme_mode') || 'system';
                document.querySelectorAll('[data-cust-theme]').forEach(function(el) {
                    var val = el.getAttribute('data-cust-theme');
                    el.classList.toggle('is-active', val === currentTheme);
                });

                var currentDir = document.documentElement.getAttribute('dir') || 'ltr';
                document.querySelectorAll('[data-cust-dir]').forEach(function(el) {
                    var val = el.getAttribute('data-cust-dir');
                    el.classList.toggle('is-active', val === currentDir);
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

                // 1. Restore saved position if valid
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

                // 3. Theme
                document.querySelectorAll('[data-cust-theme]').forEach(function(themeBtn) {
                    themeBtn.addEventListener('click', function() {
                        var mode = themeBtn.getAttribute('data-cust-theme');
                        localStorage.setItem('theme', mode);
                        localStorage.setItem('admin_theme_mode', mode);
                        var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                        document.documentElement.classList.toggle('dark', dark);
                        syncActiveStates();
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
    </script>
    @stack('scripts')
</body>
</html>
