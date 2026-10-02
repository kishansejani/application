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
            --theme-primary: {{ $sysSettings['theme_primary_color'] ?? '#000000' }};
            --theme-hover: {{ $sysSettings['theme_hover_color'] ?? '#a1a1a1' }};
            --sidebar-bg: {{ $sysSettings['sidebar_bg_color'] ?? '#000000' }};
            --sidebar-active: {{ $sysSettings['sidebar_active_color'] ?? '#add8e6' }};
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Hind Vadodara', sans-serif;
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
        .dataTables_wrapper select, .dataTables_wrapper input {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
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
        <!-- Top Navbar (Matching Screenshot Header) -->
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-30 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <!-- Left: Sidebar Toggle & Search Bar (Ctrl+/) -->
                <div class="flex items-center gap-4 flex-1">
                    <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    
                    <!-- Search Input matching screenshot -->
                    <div class="relative w-full max-w-md hidden sm:block">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input type="text" id="adminQuickSearch" placeholder="Search (Ctrl+/)"
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    </div>
                </div>

                <!-- Right Controls: Theme Mode (Light/Dark/System), Language, Storefront & Profile -->
                <div class="flex items-center gap-3">
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

                    <!-- Admin Profile Dropdown (Matching Profile with green online ring from screenshot) -->
                    <div class="relative pl-2">
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
            <!-- Sidebar Navigation (Matching Screenshot Left Bar) -->
            <aside id="adminSidebar" class="w-64 sidebar-custom-bg text-slate-300 flex-shrink-0 flex flex-col justify-between hidden lg:flex transition-all duration-300 z-20">
                <div class="p-4 space-y-1 overflow-y-auto max-h-[calc(100vh-4rem)]">
                    <!-- Brand Header in Sidebar (Matching Screenshot Decent Infoways Brand) -->
                    <div class="flex items-center justify-between px-3 py-3 mb-2 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white font-black text-base">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <span class="font-extrabold text-sm tracking-tight text-white block">{{ $sysSettings['footer_creator_name'] ?? 'Decent Infoways' }}</span>
                            </div>
                        </div>
                        <i class="fa-regular fa-circle-dot text-slate-500 text-xs"></i>
                    </div>

                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-2 mb-1">Core Navigation</p>

                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Orders -->
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.orders.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-truck-fast w-5 text-center"></i>
                            <span>Manage Orders</span>
                        </div>
                        @php $pendingCnt = \App\Models\Order::where('order_status', 'pending')->count(); @endphp
                        @if($pendingCnt > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-400 text-black">{{ $pendingCnt }}</span>
                        @endif
                    </a>

                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-4 pb-1">Catalog & Inventory</p>

                    <!-- Sliders -->
                    <a href="{{ route('admin.sliders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.sliders.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-images w-5 text-center"></i>
                        <span>Manage Sliders</span>
                    </a>

                    <!-- Categories -->
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.categories.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-layer-group w-5 text-center"></i>
                        <span>Manage Categories</span>
                    </a>

                    <!-- Sub Categories -->
                    <a href="{{ route('admin.subcategories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.subcategories.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-sitemap w-5 text-center"></i>
                        <span>Sub Categories</span>
                    </a>

                    <!-- Products -->
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.products.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                        <span>Manage Products</span>
                    </a>

                    <!-- Manage Stock -->
                    <a href="{{ route('admin.stock.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.stock.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-warehouse w-5 text-center"></i>
                            <span>Manage Stock</span>
                        </div>
                        @php $lowStockCount = \App\Models\Product::where('stock_quantity', '<=', 5)->count(); @endphp
                        @if($lowStockCount > 0)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-500 text-white">Low</span>
                        @endif
                    </a>

                    <!-- Offers & Pages -->
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-4 pb-1">Marketing & Content</p>

                    <a href="{{ route('admin.offers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.offers.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-tag w-5 text-center"></i>
                        <span>Manage Offers</span>
                    </a>

                    <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.pages.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-file-contract w-5 text-center"></i>
                        <span>Manage Pages</span>
                    </a>

                    <!-- ROLE & USER MANAGEMENT (Matching User's Screenshot Menu Group) -->
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-4 pb-1">Role & User Management</p>

                    <a href="{{ route('admin.roles.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.roles.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                            <span>Roles & Permissions</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users-gear w-5 text-center"></i>
                            <span>Users</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </a>

                    <!-- SYSTEM SETTINGS (Matching User's Screenshot Menu Group) -->
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 pt-4 pb-1">System Settings</p>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.settings.*') ? 'sidebar-active-item' : 'hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-gear w-5 text-center"></i>
                        <span>Settings</span>
                    </a>
                </div>

                <!-- Sidebar Footer & Logout -->
                <div class="p-4 border-t border-slate-800 bg-black/40">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-white/10 hover:bg-rose-600/30 text-rose-300 hover:text-rose-200 rounded-xl text-xs font-bold transition">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Logout Account</span>
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

        $(document).on('click', function() {
            $('#themeDropdownMenu').addClass('hidden');
        });

        // Mobile Sidebar toggle
        $('#sidebarToggle').on('click', function() {
            $('#adminSidebar').toggleClass('hidden');
        });

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
