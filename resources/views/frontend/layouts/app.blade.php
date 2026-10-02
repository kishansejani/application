<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('messages.store_name')) - {{ __('messages.tagline') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Hind+Vadodara:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Leaflet CSS for Map Picker -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Toastr & SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
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
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Hind Vadodara"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', 'Hind Vadodara', sans-serif; }
        .heart-active { color: #f43f5e !important; fill: #f43f5e !important; }
        #mapPickerContainer { height: 320px; width: 100%; border-radius: 1rem; }
    </style>
    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Top Announcement Bar: 2-Hour Express Delivery Promise -->
    <div class="bg-gradient-to-r from-emerald-700 via-brand-700 to-teal-800 text-white text-xs font-semibold py-2 px-4 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>⚡ {{ __('messages.delivery_promise_title') }}:</span>
                <span class="hidden sm:inline opacity-90">{{ __('messages.delivery_promise_desc') }}</span>
            </div>
            <div class="flex items-center gap-4 text-[11px]">
                <a href="{{ route('order.track') }}" class="hover:underline hidden md:flex items-center gap-1">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Track Order</span>
                </a>
                <span class="opacity-50">|</span>
                <!-- Language Toggle -->
                <a href="{{ route('lang.switch', app()->getLocale() === 'gu' ? 'en' : 'gu') }}" class="flex items-center gap-1.5 font-bold hover:text-amber-300 transition-colors">
                    <i class="fa-solid fa-language text-sm"></i>
                    <span>{{ app()->getLocale() === 'gu' ? 'English' : 'ગુજરાતી' }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-brand-500/25">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-slate-900 to-brand-700 bg-clip-text text-transparent">FreshExpress</span>
                        <span class="block text-[10px] uppercase font-bold tracking-widest text-emerald-600 leading-none">
                            {{ app()->getLocale() === 'gu' ? '૨ કલાક ડિલિવરી' : '2-Hour Delivery' }}
                        </span>
                    </div>
                </a>

                <!-- Search Bar -->
                <div class="flex-1 max-w-xl mx-4 hidden md:block">
                    <form action="{{ route('products.index') }}" method="GET" class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder') }}"
                            class="w-full pl-11 pr-24 py-3 bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 focus:border-brand-500 rounded-2xl text-xs sm:text-sm focus:outline-none focus:ring-4 focus:ring-brand-500/10 transition-all">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <button type="submit" class="absolute right-2 top-1.5 bottom-1.5 px-4 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition-colors">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Right Actions (Offers, Wishlist, Cart Drawer, User Profile) -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Offers Link -->
                    <a href="{{ route('pages.offers') }}" class="hidden lg:flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-200 transition-colors">
                        <i class="fa-solid fa-tag text-amber-500"></i>
                        <span>{{ __('messages.offers') }}</span>
                    </a>

                    <!-- Wishlist Button -->
                    <a href="{{ route('wishlist.index') }}" class="relative p-2.5 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="{{ __('messages.wishlist') }}">
                        <i class="fa-solid fa-heart text-lg"></i>
                        @auth
                            @php $wishCount = Auth::user()->wishlists()->count(); @endphp
                            @if($wishCount > 0)
                                <span id="globalWishlistBadge" class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center shadow">
                                    {{ $wishCount }}
                                </span>
                            @endif
                        @endauth
                    </a>

                    <!-- Cart Drawer Trigger Button -->
                    <button onclick="openCartDrawer()" class="flex items-center gap-2.5 px-3.5 py-2.5 bg-brand-50 hover:bg-brand-100 border border-brand-200 text-brand-900 rounded-2xl transition-all shadow-sm">
                        <div class="relative text-brand-700">
                            <i class="fa-solid fa-cart-shopping text-lg"></i>
                            @php
                                $cartQty = 0;
                                if(Auth::check()) {
                                    $cartQty = \App\Models\CartItem::where('user_id', Auth::id())->sum('quantity');
                                } else {
                                    $cartQty = \App\Models\CartItem::where('session_id', Session::getId())->sum('quantity');
                                }
                            @endphp
                            <span id="globalCartBadge" class="absolute -top-2 -right-2.5 min-w-[1.25rem] h-5 px-1 rounded-full bg-brand-600 text-white text-[10px] font-extrabold flex items-center justify-center shadow">
                                {{ $cartQty }}
                            </span>
                        </div>
                        <span class="hidden sm:inline text-xs font-bold text-brand-900">{{ __('messages.cart') }}</span>
                    </button>

                    <!-- User Account / Login Button -->
                    @auth
                        <div class="relative group">
                            <button class="flex items-center gap-2 p-1.5 rounded-2xl hover:bg-slate-100 transition-colors">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ substr(Auth::user()->name ?: 'User', 0, 1) }}
                                </div>
                                <span class="hidden xl:inline text-xs font-bold text-slate-800">{{ Str::limit(Auth::user()->name, 12) }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>

                            <!-- User Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 hidden group-hover:block transition-all z-50">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-xs font-bold text-slate-900">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">+91 {{ Auth::user()->phone }}</p>
                                </div>
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-medium">
                                    <i class="fa-solid fa-truck-fast text-brand-600 w-4"></i>
                                    <span>{{ __('messages.order_history') }}</span>
                                </a>
                                <a href="{{ route('addresses.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-medium">
                                    <i class="fa-solid fa-location-dot text-brand-600 w-4"></i>
                                    <span>{{ __('messages.saved_addresses') }}</span>
                                </a>
                                <a href="{{ route('customer.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-medium">
                                    <i class="fa-solid fa-user-pen text-brand-600 w-4"></i>
                                    <span>{{ __('messages.my_account') }}</span>
                                </a>
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-indigo-700 bg-indigo-50 hover:bg-indigo-100 font-bold border-t border-b border-indigo-100">
                                        <i class="fa-solid fa-gauge-high text-indigo-600 w-4"></i>
                                        <span>{{ __('messages.admin_dashboard') }}</span>
                                    </a>
                                @endif
                                <form action="{{ route('customer.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-bold text-left">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-rose-500 w-4"></i>
                                        <span>{{ __('messages.logout') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('customer.login') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-bold shadow-md transition-all flex items-center gap-2">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <span>{{ __('messages.login') }}</span>
                        </a>
                    @endauth
                </div>

            </div>

            <!-- Category Navigation Sub-bar -->
            <div class="py-2.5 flex items-center justify-between border-t border-slate-100 overflow-x-auto text-xs font-semibold text-slate-600 no-scrollbar">
                <div class="flex items-center gap-6 shrink-0">
                    <a href="{{ route('home') }}" class="hover:text-brand-600 {{ request()->routeIs('home') ? 'text-brand-700 font-bold' : '' }}">
                        {{ __('messages.home') }}
                    </a>
                    <a href="{{ route('categories.index') }}" class="hover:text-brand-600 {{ request()->routeIs('categories.*') ? 'text-brand-700 font-bold' : '' }}">
                        {{ __('messages.all_categories') }}
                    </a>
                    <a href="{{ route('products.index') }}" class="hover:text-brand-600 {{ request()->routeIs('products.*') ? 'text-brand-700 font-bold' : '' }}">
                        {{ __('messages.products') }}
                    </a>
                    <a href="{{ route('pages.offers') }}" class="hover:text-brand-600 text-amber-700 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-bolt text-amber-500"></i>
                        <span>{{ __('messages.offers') }}</span>
                    </a>
                    <a href="{{ route('pages.show', 'about-us') }}" class="hover:text-brand-600">
                        {{ __('messages.about_us') }}
                    </a>
                    <a href="{{ route('order.track') }}" class="hover:text-brand-600">
                        Track Order
                    </a>
                </div>

                <div class="hidden lg:flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                    <i class="fa-solid fa-clock text-brand-600"></i>
                    <span>Order before 12:00 PM for Express Delivery</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        <!-- Flash messages -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="max-w-7xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-info text-amber-500 text-base"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-base"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs pt-16 pb-12 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 pb-12 border-b border-slate-800">
                <!-- Col 1 & 2: About Store -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-lg text-white tracking-tight">FreshExpress Grocery</span>
                            <span class="block text-[10px] text-emerald-400 font-bold uppercase">ફ્રેશ એક્સપ્રેસ સ્ટોર</span>
                        </div>
                    </div>
                    <p class="text-slate-400 leading-relaxed text-xs pr-6">
                        {{ app()->getLocale() === 'gu'
                            ? 'ખેતરમાંથી સીધા તાજા શાકભાજી, ફળો, દેશી ડેરી અને સ્વાદિષ્ટ ગુજરાતી નાસ્તો તમારા ઘર સુધી પહોંચાડતો વિશ્વસનીય સ્ટોર. બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં ડિલિવરી મેળવો.'
                            : 'Farm-fresh fruits, vegetables, pure organic dairy, authentic snacks, and grocery delivered straight to your door with 2-hour express delivery for orders before 12 PM.'
                        }}
                    </p>
                    <div class="flex items-center gap-3 text-slate-300">
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:bg-brand-600 transition-colors"><i class="fa-brands fa-whatsapp"></i></span>
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:bg-brand-600 transition-colors"><i class="fa-brands fa-instagram"></i></span>
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:bg-brand-600 transition-colors"><i class="fa-brands fa-facebook-f"></i></span>
                    </div>
                </div>

                <!-- Col 3: Quick Links -->
                <div>
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4">{{ __('messages.quick_links') }}</h5>
                    <ul class="space-y-2.5 font-medium">
                        <li><a href="{{ route('home') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.home') }}</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.products') }}</a></li>
                        <li><a href="{{ route('categories.index') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.all_categories') }}</a></li>
                        <li><a href="{{ route('pages.offers') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.offers') }}</a></li>
                        <li><a href="{{ route('order.track') }}" class="hover:text-brand-400 transition-colors">Track Delivery Status</a></li>
                    </ul>
                </div>

                <!-- Col 4: Legal & Policies -->
                <div>
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4">Legal & Information</h5>
                    <ul class="space-y-2.5 font-medium">
                        <li><a href="{{ route('pages.show', 'about-us') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.about_us') }}</a></li>
                        <li><a href="{{ route('pages.show', 'legal-information') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.legal_info') }}</a></li>
                        <li><a href="{{ route('pages.show', 'privacy-policy') }}" class="hover:text-brand-400 transition-colors">{{ __('messages.privacy_policy') }}</a></li>
                        <li><a href="{{ route('admin.login') }}" class="text-slate-500 hover:text-slate-300 font-bold transition-colors">Admin Portal &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 5: Customer Support -->
                <div>
                    <h5 class="text-white font-bold uppercase tracking-wider text-xs mb-4">{{ __('messages.customer_support') }}</h5>
                    <p class="text-xs text-slate-400 mb-2">Have questions about your order?</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold">
                            <i class="fa-solid fa-phone"></i>
                            <span>+91 98765 43210</span>
                        </div>
                        <div class="text-slate-400">support@freshexpress.in</div>
                        <div class="text-[11px] text-slate-500 mt-2">
                            Operating: 7:00 AM - 10:00 PM<br>
                            Ahmedabad & Gujarat Metro
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} FreshExpress Grocery Store. All rights reserved.</p>
                <div class="flex items-center gap-3">
                    <span>FSSAI Certified: 10721026000123</span>
                    <span>•</span>
                    <span>100% Safe Payments (COD / UPI)</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Slide-over Mini-Cart Drawer -->
    <div id="cartDrawer" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div onclick="closeCartDrawer()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl flex flex-col z-50 animate-slide-left">
            <!-- Drawer Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">{{ __('messages.shopping_cart') }}</h3>
                        <p id="drawerItemCount" class="text-[11px] text-slate-500 font-semibold">0 items</p>
                    </div>
                </div>
                <button onclick="closeCartDrawer()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-200/60">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Delivery Slot Pill inside Drawer -->
            <div class="p-3 bg-amber-50 border-b border-amber-100 flex items-center gap-2 text-xs text-amber-900 font-semibold">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span id="drawerDeliverySlot">Express 2-Hour Delivery (Before 12 PM)</span>
            </div>

            <!-- Drawer Items Body -->
            <div id="drawerItemsList" class="flex-1 overflow-y-auto p-5 space-y-4">
                <!-- Injected via Javascript -->
            </div>

            <!-- Drawer Footer -->
            <div class="p-5 border-t border-slate-100 bg-slate-50 space-y-3">
                <div class="flex justify-between items-center text-sm font-bold text-slate-900">
                    <span>{{ __('messages.item_total') }}</span>
                    <span id="drawerSubtotal" class="text-brand-700 text-base font-extrabold">₹0.00</span>
                </div>
                <p class="text-[11px] text-slate-500">Taxes and delivery calculated at checkout.</p>
                
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ route('cart.index') }}" class="w-full py-3 bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-xl font-bold text-xs text-center transition-colors">
                        View Full Cart
                    </a>
                    <a href="{{ route('checkout.index') }}" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs text-center shadow-lg shadow-brand-500/25 transition-all">
                        {{ __('messages.proceed_to_checkout') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div onclick="closeQuickView()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl z-10 overflow-hidden">
            <button onclick="closeQuickView()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <div class="w-full h-64 rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                    <img id="qvImage" src="" class="w-full h-full object-cover">
                </div>
                <div class="space-y-3">
                    <span id="qvCategory" class="px-2.5 py-1 rounded-md bg-brand-50 text-brand-700 font-bold text-[10px] uppercase tracking-wider"></span>
                    <h3 id="qvTitle" class="text-xl font-extrabold text-slate-900"></h3>
                    <p id="qvUnit" class="text-xs text-slate-500 font-semibold"></p>

                    <div class="flex items-baseline gap-2 pt-2">
                        <span id="qvPrice" class="text-2xl font-extrabold text-slate-900"></span>
                        <span id="qvMrp" class="text-sm text-slate-400 line-through"></span>
                        <span id="qvDiscount" class="text-xs font-bold text-emerald-600"></span>
                    </div>

                    <p id="qvDescription" class="text-xs text-slate-600 leading-relaxed"></p>

                    <div class="pt-4 flex items-center gap-3">
                        <input type="number" id="qvQty" value="1" min="1" class="w-16 px-3 py-2.5 border border-slate-200 rounded-xl text-center font-bold text-sm focus:outline-none">
                        <button id="qvAddToCartBtn" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs shadow-md shadow-brand-500/20 transition-all">
                            {{ __('messages.add_to_cart') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
        };

        // Add to cart Global Ajax
        function addToCart(productId, quantity = 1) {
            $.ajax({
                url: '{{ route("cart.add") }}',
                type: 'POST',
                data: { product_id: productId, quantity: quantity },
                success: function(res) {
                    if (res.success) {
                        $('#globalCartBadge').text(res.cart_count);
                        toastr.success(res.message);
                        openCartDrawer();
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Error adding to cart';
                    toastr.error(msg);
                }
            });
        }

        // Toggle Wishlist Global Ajax
        function toggleWishlist(productId, btn) {
            $.ajax({
                url: '{{ route("wishlist.toggle") }}',
                type: 'POST',
                data: { product_id: productId },
                success: function(res) {
                    if (res.success) {
                        if (res.in_wishlist) {
                            $(btn).find('i').addClass('heart-active text-rose-600');
                            toastr.success(res.message);
                        } else {
                            $(btn).find('i').removeClass('heart-active text-rose-600');
                            toastr.info(res.message);
                        }
                        if (res.wishlist_count !== undefined) {
                            $('#globalWishlistBadge').text(res.wishlist_count);
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        window.location.href = '{{ route("customer.login") }}';
                    } else {
                        toastr.error('Could not update wishlist');
                    }
                }
            });
        }

        // Slide-over Drawer methods
        function openCartDrawer() {
            $('#cartDrawer').removeClass('hidden');
            loadDrawerContent();
        }

        function closeCartDrawer() {
            $('#cartDrawer').addClass('hidden');
        }

        function loadDrawerContent() {
            $.get('{{ route("cart.drawer") }}', function(data) {
                $('#drawerItemCount').text(`${data.count} items`);
                $('#drawerSubtotal').text(data.formatted_subtotal);
                $('#drawerDeliverySlot').text(data.delivery_slot);
                $('#globalCartBadge').text(data.count);

                const list = $('#drawerItemsList');
                list.empty();

                if (data.items.length === 0) {
                    list.html(`
                        <div class="text-center py-12 text-slate-400">
                            <i class="fa-solid fa-basket-shopping text-4xl mb-3 text-slate-300"></i>
                            <p class="font-bold text-slate-700 text-sm">{{ __('messages.your_cart_is_empty') }}</p>
                            <a href="{{ route('products.index') }}" onclick="closeCartDrawer()" class="inline-block mt-3 px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">{{ __('messages.start_shopping') }}</a>
                        </div>
                    `);
                    return;
                }

                data.items.forEach(function(item) {
                    list.append(`
                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-slate-100 bg-slate-50/50">
                            <img src="${item.thumbnail}" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0">
                            <div class="flex-1 min-w-0">
                                <h5 class="text-xs font-bold text-slate-900 truncate">${item.name}</h5>
                                <p class="text-[11px] text-slate-400">${item.unit} • ₹${item.price.toFixed(2)}</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <button onclick="updateCartItem(${item.id}, ${item.quantity - 1})" class="w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center hover:bg-slate-100">-</button>
                                    <span class="text-xs font-bold text-slate-800">${item.quantity}</span>
                                    <button onclick="updateCartItem(${item.id}, ${item.quantity + 1})" class="w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center hover:bg-slate-100">+</button>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-extrabold text-slate-900">₹${item.subtotal.toFixed(2)}</div>
                                <button onclick="removeCartItem(${item.id})" class="text-[10px] text-rose-500 hover:underline mt-2">Remove</button>
                            </div>
                        </div>
                    `);
                });
            });
        }

        function updateCartItem(itemId, qty) {
            $.ajax({
                url: `/cart/update/${itemId}`,
                type: 'PATCH',
                data: { quantity: qty },
                success: function(res) {
                    if (res.success) {
                        loadDrawerContent();
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error updating item');
                }
            });
        }

        function removeCartItem(itemId) {
            $.ajax({
                url: `/cart/remove/${itemId}`,
                type: 'DELETE',
                success: function(res) {
                    if (res.success) {
                        toastr.info(res.message);
                        loadDrawerContent();
                    }
                }
            });
        }

        // Quick View Modal
        let activeQvProductId = null;
        function openQuickView(productId) {
            activeQvProductId = productId;
            $.get(`/product/quick-view/${productId}`, function(p) {
                $('#qvImage').attr('src', p.thumbnail);
                $('#qvCategory').text(p.category);
                $('#qvTitle').text(p.name);
                $('#qvUnit').text(p.unit);
                $('#qvPrice').text('₹' + Number(p.effective_price).toFixed(2));
                if (p.discount_price) {
                    $('#qvMrp').text('₹' + Number(p.price).toFixed(2));
                    $('#qvDiscount').text(`(${p.discount_percent}% OFF)`);
                } else {
                    $('#qvMrp').text('');
                    $('#qvDiscount').text('');
                }
                $('#qvDescription').text(p.short_desc || '');
                $('#qvQty').val(1);
                $('#quickViewModal').removeClass('hidden');
            });
        }

        function closeQuickView() {
            $('#quickViewModal').addClass('hidden');
        }

        $('#qvAddToCartBtn').on('click', function() {
            if (activeQvProductId) {
                const qty = parseInt($('#qvQty').val()) || 1;
                addToCart(activeQvProductId, qty);
                closeQuickView();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
