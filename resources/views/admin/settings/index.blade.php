@extends('admin.layouts.admin')

@section('title', 'Settings & Color Management')

@section('content')
@php
    $s = fn ($key, $default) => old($key, $settings[$key] ?? $default);
    
    // Admin Panel Color Groups
    $adminColorGroups = [
        'Buttons' => [
            ['btn_primary_bg',    'btnPrimaryBgPicker',    'btnPrimaryBgText',    'Primary button background', 'Save, submit and other primary actions.', '#0f172a'],
            ['btn_primary_text',  'btnPrimaryTextPicker',  'btnPrimaryTextText',  'Primary button text',       'Text and icon colour inside primary buttons.', '#ffffff'],
            ['btn_primary_hover', 'btnPrimaryHoverPicker', 'btnPrimaryHoverText', 'Primary button hover',      'Background while hovering a primary button.', '#1e293b'],
            ['btn_accent_bg',     'btnAccentBgPicker',     'btnAccentBgText',     'Accent button background',  'Add, create and success actions.', '#10b981'],
            ['btn_accent_text',   'btnAccentTextPicker',   'btnAccentTextText',   'Accent button text',        'Text and icon colour inside accent buttons.', '#ffffff'],
        ],
        'Sidebar' => [
            ['sidebar_bg_color',     'sidebarBgPicker',     'sidebarBgText',     'Sidebar background',  'Background of the left navigation.', '#000000'],
            ['sidebar_active_color', 'sidebarActivePicker', 'sidebarActiveText', 'Active menu item',    'Highlight for the current page link.', '#add8e6'],
            ['sidebar_text_color',   'sidebarTextPicker',   'sidebarTextText',   'Sidebar text',        'Menu labels and icons.', '#ffffff'],
        ],
        'Brand' => [
            ['theme_primary_color', 'primaryColorPicker', 'primaryColorText', 'Brand colour',        'Focus rings and brand highlights across the panel.', '#0f172a'],
            ['theme_hover_color',   'hoverColorPicker',   'hoverColorText',   'Hover / link colour', 'Links and subtle interactive states.', '#334155'],
        ],
    ];

    // Frontend Web Colors — Light Mode
    $frontLightGroups = [
        'Brand & Accents' => [
            ['front_brand_primary', 'fBrandPriPicker',   'fBrandPriText',   'Brand primary',      'Main header accents, active badges & highlights.', '#059669'],
            ['front_brand_hover',   'fBrandHovPicker',   'fBrandHovText',   'Brand hover',        'Hover state for brand links and elements.', '#047857'],
            ['front_brand_light',   'fBrandLtPicker',    'fBrandLtText',    'Brand soft tint',    'Soft background pill & badge tint.', '#ecfdf5'],
            ['front_accent_color',  'fAccentPicker',     'fAccentText',     'Accent highlight',   'Selected dropdowns, icons & steppers.', '#10b981'],
        ],
        'Buttons' => [
            ['front_btn_primary_bg',    'fBtnPriBgPicker',  'fBtnPriBgText',  'Primary button bg',    'Add to cart, checkout, primary CTA buttons.', '#059669'],
            ['front_btn_primary_text',  'fBtnPriTxtPicker', 'fBtnPriTxtText', 'Primary button text',  'Text & icon colour on primary buttons.', '#ffffff'],
            ['front_btn_primary_hover', 'fBtnPriHovPicker', 'fBtnPriHovText', 'Primary button hover', 'Hover background on primary buttons.', '#047857'],
            ['front_btn_accent_bg',     'fBtnAccBgPicker',  'fBtnAccBgText',  'Secondary button bg',  'Stepper buttons & secondary actions.', '#10b981'],
            ['front_btn_accent_text',   'fBtnAccTxtPicker', 'fBtnAccTxtText', 'Secondary button text','Text on secondary & stepper buttons.', '#ffffff'],
        ],
        'Surfaces & Layout' => [
            ['front_topbar_bg',    'fTopBgPicker',     'fTopBgText',     'Utility topbar bg',    'Top promo & language/theme utility bar.', '#064e3b'],
            ['front_topbar_text',  'fTopTxtPicker',    'fTopTxtText',    'Utility topbar text',  'Text & icons inside top utility bar.', '#ecfdf5'],
            ['front_header_bg',    'fHeadBgPicker',    'fHeadBgText',    'Main header bg',       'Sticky top navigation & search bar header.', '#ffffff'],
            ['front_body_bg',      'fBodyBgPicker',    'fBodyBgText',    'Page background',      'Storefront body surface background.', '#f6f8f7'],
            ['front_card_bg',      'fCardBgPicker',    'fCardBgText',    'Card & box surface',   'Product cards, checkout panels & drawers.', '#ffffff'],
            ['front_card_border',  'fCardBrdPicker',   'fCardBrdText',   'Card & line border',   'Dividers, borders & container outlines.', '#e2e8f0'],
            ['front_footer_bg',    'fFootBgPicker',    'fFootBgText',    'Footer background',    'Main footer area at page bottom.', '#0f172a'],
            ['front_footer_text',  'fFootTxtPicker',   'fFootTxtText',   'Footer text',          'Links, categories & copyright text.', '#94a3b8'],
        ],
        'Text & Form Controls' => [
            ['front_text_primary', 'fTxtPriPicker',    'fTxtPriText',    'Primary text',         'Product titles, headings & primary text.', '#0f172a'],
            ['front_text_muted',   'fTxtMutPicker',    'fTxtMutText',    'Muted / hint text',    'Subtitles, timestamps & placeholder text.', '#64748b'],
            ['front_input_bg',     'fInpBgPicker',     'fInpBgText',     'Input & search bg',    'Search input, checkout form fields.', '#ffffff'],
            ['front_input_border', 'fInpBrdPicker',    'fInpBrdText',    'Input field border',   'Form input & select borders.', '#cbd5e1'],
            ['front_dropdown_bg',  'fDropBgPicker',    'fDropBgText',    'Dropdown menu bg',     'All categories menu & custom dropdowns.', '#ffffff'],
        ],
    ];

    // Frontend Web Colors — Dark Mode
    $frontDarkGroups = [
        'Dark Brand & Accents' => [
            ['front_dark_brand_primary', 'fdBrandPriPicker', 'fdBrandPriText', 'Dark brand primary',   'Main header accents & highlights in dark mode.', '#34d399'],
            ['front_dark_brand_hover',   'fdBrandHovPicker', 'fdBrandHovText', 'Dark brand hover',     'Hover state for links in dark mode.', '#6ee7b7'],
            ['front_dark_brand_light',   'fdBrandLtPicker',  'fdBrandLtText',  'Dark brand soft tint', 'Soft background tint in dark mode.', '#064e3b'],
            ['front_dark_accent_color',  'fdAccentPicker',   'fdAccentText',   'Dark accent highlight','Selected items & stepper in dark mode.', '#10b981'],
        ],
        'Dark Buttons' => [
            ['front_dark_btn_primary_bg',    'fdBtnPriBgPicker',  'fdBtnPriBgText',  'Dark primary btn bg',   'Primary button background in dark mode.', '#059669'],
            ['front_dark_btn_primary_text',  'fdBtnPriTxtPicker', 'fdBtnPriTxtText', 'Dark primary btn text', 'Text on primary button in dark mode.', '#ffffff'],
            ['front_dark_btn_primary_hover', 'fdBtnPriHovPicker', 'fdBtnPriHovText', 'Dark primary btn hover','Hover background in dark mode.', '#10b981'],
        ],
        'Dark Surfaces & Layout' => [
            ['front_dark_body_bg',      'fdBodyBgPicker',   'fdBodyBgText',   'Dark page background', 'Storefront dark mode body background.', '#020617'],
            ['front_dark_card_bg',      'fdCardBgPicker',   'fdCardBgText',   'Dark card surface',    'Product cards & panels in dark mode.', '#0f172a'],
            ['front_dark_card_border',  'fdCardBrdPicker',  'fdCardBrdText',  'Dark card border',     'Container & divider borders in dark mode.', '#1e293b'],
            ['front_dark_header_bg',    'fdHeadBgPicker',   'fdHeadBgText',   'Dark header bg',       'Sticky header background in dark mode.', '#0b1120'],
            ['front_dark_topbar_bg',    'fdTopBgPicker',    'fdTopBgText',    'Dark topbar bg',       'Top utility bar background in dark mode.', '#020617'],
            ['front_dark_topbar_text',  'fdTopTxtPicker',   'fdTopTxtText',   'Dark topbar text',     'Top utility bar text in dark mode.', '#94a3b8'],
            ['front_dark_footer_bg',    'fdFootBgPicker',   'fdFootBgText',   'Dark footer bg',       'Footer surface in dark mode.', '#020617'],
            ['front_dark_footer_text',  'fdFootTxtPicker',  'fdFootTxtText',  'Dark footer text',     'Footer links & copyright in dark mode.', '#64748b'],
        ],
        'Dark Text & Form Controls' => [
            ['front_dark_text_primary', 'fdTxtPriPicker',   'fdTxtPriText',   'Dark primary text',    'Headings and product names in dark mode.', '#f1f5f9'],
            ['front_dark_text_muted',   'fdTxtMutPicker',   'fdTxtMutText',   'Dark muted text',      'Subtext and labels in dark mode.', '#94a3b8'],
            ['front_dark_input_bg',     'fdInpBgPicker',    'fdInpBgText',    'Dark input bg',        'Search bar & form field bg in dark mode.', '#0b1324'],
            ['front_dark_input_border', 'fdInpBrdPicker',   'fdInpBrdText',   'Dark input border',    'Input field borders in dark mode.', '#334155'],
            ['front_dark_dropdown_bg',  'fdDropBgPicker',   'fdDropBgText',   'Dark dropdown menu bg','Dropdown menus & popovers in dark mode.', '#0f172a'],
        ],
    ];

    $adminPresets = [
        ['Midnight slate', 'Classic dark and clean', ['#0F172A', '#1E293B', '#10B981', '#000000', '#ADD8E6', '#0F172A', '#334155']],
        ['Emerald fresh',  'Grocery and organic',    ['#059669', '#047857', '#0284C7', '#064E3B', '#6EE7B7', '#059669', '#047857']],
        ['Royal indigo',   'Modern tech',            ['#4338CA', '#3730A3', '#06B6D4', '#1E1B4B', '#C7D2FE', '#4338CA', '#3730A3']],
        ['Luxury violet',  'Premium and bold',       ['#7C3AED', '#6D28D9', '#EC4899', '#0F172A', '#E9D5FF', '#7C3AED', '#6D28D9']],
        ['Amber gold',     'Warm e-commerce',        ['#D97706', '#B45309', '#EF4444', '#18181B', '#FDE68A', '#D97706', '#B45309']],
    ];

    $frontPresets = [
        [
            'name' => 'Fresh Emerald (Default)',
            'desc' => 'Classic crisp green grocery theme',
            'colors' => ['#059669', '#047857', '#ECFDF5', '#10B981', '#059669', '#064E3B', '#F6F8F7', '#FFFFFF', '#E2E8F0', '#0F172A'],
            'dark'   => ['#34D399', '#6EE7B7', '#064E3B', '#10B981', '#059669', '#020617', '#0F172A', '#1E293B', '#0B1120', '#F1F5F9'],
        ],
        [
            'name' => 'Ocean Blue',
            'desc' => 'Cool, modern and trustworthy blue',
            'colors' => ['#0284C7', '#0369A1', '#E0F2FE', '#0EA5E9', '#0284C7', '#0C4A6E', '#F0F9FF', '#FFFFFF', '#BAE6FD', '#0F172A'],
            'dark'   => ['#38BDF8', '#7DD3FC', '#0C4A6E', '#0EA5E9', '#0284C7', '#020817', '#0F172A', '#1E293B', '#082F49', '#F1F5F9'],
        ],
        [
            'name' => 'Royal Purple',
            'desc' => 'Vibrant, premium shopping experience',
            'colors' => ['#7C3AED', '#6D28D9', '#F5F3FF', '#8B5CF6', '#7C3AED', '#4C1D95', '#FAF5FF', '#FFFFFF', '#DDD6FE', '#0F172A'],
            'dark'   => ['#A78BFA', '#C4B5FD', '#4C1D95', '#8B5CF6', '#7C3AED', '#090514', '#150D2A', '#2E1065', '#1F1147', '#F5F3FF'],
        ],
        [
            'name' => 'Sunset Orange',
            'desc' => 'Warm, appetizing and vibrant tone',
            'colors' => ['#EA580C', '#C2410C', '#FFF7ED', '#F97316', '#EA580C', '#7C2D12', '#FFFBF5', '#FFFFFF', '#FED7AA', '#18181B'],
            'dark'   => ['#FB923C', '#FDBA74', '#7C2D12', '#F97316', '#EA580C', '#0C0A09', '#1C1917', '#292524', '#1C1917', '#FAFAF9'],
        ],
        [
            'name' => 'Crimson Rose',
            'desc' => 'Bold, stylish and high energy',
            'colors' => ['#E11D48', '#BE123C', '#FFF1F2', '#F43F5E', '#E11D48', '#881337', '#FFF5F6', '#FFFFFF', '#FECDD3', '#0F172A'],
            'dark'   => ['#FB7185', '#FDA4AF', '#881337', '#F43F5E', '#E11D48', '#0D0407', '#1A0B10', '#2E111C', '#1A0B10', '#FFF1F2'],
        ],
    ];

    $mode = old('theme_mode', $settings['theme_mode'] ?? 'system');
@endphp

    <x-admin.page-header title="Settings & Color Management" subtitle="Centralized color system, branding and appearance controls across the entire system." icon="gear-six">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.reset') }}" class="btn btn-outline" data-confirm="All colours (Admin & Frontend Light/Dark), footer text and the store name will return to their default values." data-confirm-title="Reset all settings to defaults?" data-confirm-button="Reset All" data-confirm-danger>
                <i class="ph ph-arrow-counter-clockwise"></i> Reset Defaults
            </a>
            <button type="submit" form="settingsForm" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Save Settings</button>
        </div>
    </x-admin.page-header>

    {{-- Navigation Tabs --}}
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 mb-6 overflow-x-auto no-scrollbar">
        <button type="button" class="tab-btn is-active flex items-center gap-2 px-4 py-3 font-bold text-sm border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 -mb-px transition" data-tab-target="tabAdminTheme">
            <i class="ph-duotone ph-shield-check text-lg"></i>
            <span>Admin Console Theme</span>
        </button>
        @if($isSuperAdmin)
            <button type="button" class="tab-btn flex items-center gap-2 px-4 py-3 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white -mb-px transition" data-tab-target="tabFrontTheme">
                <i class="ph-duotone ph-storefront text-lg"></i>
                <span>Frontend Web Colors</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">Super Admin</span>
            </button>
            <button type="button" class="tab-btn flex items-center gap-2 px-4 py-3 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white -mb-px transition" data-tab-target="tabFrontDarkTheme">
                <i class="ph-duotone ph-moon-stars text-lg"></i>
                <span>Dark Mode Colors</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-300">Dark Theme</span>
            </button>
        @endif
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
        @csrf

        {{-- ============================== TAB 1: ADMIN CONSOLE THEME ============================== --}}
        <div id="tabAdminTheme" class="tab-pane space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
                <div class="xl:col-span-2 space-y-5 min-w-0">

                    {{-- General --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-storefront"></i> General Details</h3>
                                <p class="card-subtitle">Shown in the sidebar, browser tab title, invoices and exports.</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <label for="storeName" class="form-label">Store / Panel Name</label>
                            <input type="text" id="storeName" name="store_name" value="{{ $s('store_name', 'Fresh Express') }}" maxlength="60" placeholder="Fresh Express" class="form-control{{ $errors->has('store_name') ? ' !border-rose-400' : '' }}">
                            <p class="form-hint">Displayed across the top navigation and storefront.</p>
                            @error('store_name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Presets --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-swatches"></i> Admin Theme Presets</h3>
                                <p class="card-subtitle">Apply a curated palette to the admin console in one click.</p>
                            </div>
                        </div>
                        <div class="card-body grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            @foreach($adminPresets as [$pName, $pHint, $c])
                                <button type="button" onclick="applyAdminPreset('{{ implode("', '", $c) }}')" class="group text-left p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 hover:border-slate-400 dark:hover:border-slate-500 hover:shadow-soft transition {{ $loop->last ? 'col-span-2 sm:col-span-1' : '' }}">
                                    <span class="flex h-9 rounded-lg overflow-hidden mb-2.5 border border-black/5">
                                        <span class="w-1/3" style="background: {{ $c[3] }}"></span>
                                        <span class="w-1/3" style="background: {{ $c[0] }}"></span>
                                        <span class="w-1/6" style="background: {{ $c[2] }}"></span>
                                        <span class="w-1/6" style="background: {{ $c[4] }}"></span>
                                    </span>
                                    <span class="block text-[12.5px] font-bold text-slate-900 dark:text-white">{{ $pName }}</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ $pHint }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Admin Colours --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-palette"></i> Admin Color Palette</h3>
                                <p class="card-subtitle">Changes preview live on this page. Save to apply them across the console.</p>
                            </div>
                            <span class="badge badge-success badge-dot">Live preview</span>
                        </div>
                        <div class="card-body space-y-6">
                            @foreach($adminColorGroups as $groupName => $fields)
                                <div>
                                    <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3"><i class="ph ph-circle text-xs text-emerald-500"></i> {{ $groupName }}</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                                        @foreach($fields as [$key, $pickerId, $textId, $label, $hint, $default])
                                            @php $val = $s($key, $default); @endphp
                                            <div>
                                                <label for="{{ $textId }}" class="form-label">{{ $label }} @if($key !== 'sidebar_text_color')<span class="text-rose-500">*</span>@endif</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" id="{{ $pickerId }}" value="{{ preg_match('/^#[0-9a-fA-F]{6}$/', $val) ? strtolower($val) : $default }}" aria-label="{{ $label }} picker"
                                                           class="w-11 h-10 shrink-0 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                                                    <input type="text" id="{{ $textId }}" name="{{ $key }}" value="{{ $val }}" maxlength="20" @if($key !== 'sidebar_text_color') required @endif spellcheck="false"
                                                           class="form-control font-mono uppercase{{ $errors->has($key) ? ' !border-rose-400' : '' }}">
                                                </div>
                                                <p class="form-hint">{{ $hint }}</p>
                                                @error($key)<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Appearance --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-monitor"></i> Default Theme Mode</h3>
                                <p class="card-subtitle">Initial default mode for users before manual toggle.</p>
                            </div>
                        </div>
                        <div class="card-body grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach([['light', 'Light', 'sun', 'Bright, crisp high-contrast surfaces'], ['dark', 'Dark', 'moon', 'Easy on the eyes in low light'], ['system', 'System', 'desktop', 'Follows device/OS appearance']] as [$mVal, $mLabel, $mIcon, $mHint])
                                <label class="mode-tile flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer transition">
                                    <input type="radio" name="theme_mode" value="{{ $mVal }}" {{ $mode === $mVal ? 'checked' : '' }} class="mt-1 w-4 h-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" data-theme-radio>
                                    <span>
                                        <span class="flex items-center gap-1.5 text-[13px] font-bold text-slate-900 dark:text-white"><i class="ph-duotone ph-{{ $mIcon }} text-base"></i> {{ $mLabel }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $mHint }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-copyright"></i> Footer Credits</h3>
                                <p class="card-subtitle">Text shown at the bottom of the admin console.</p>
                            </div>
                        </div>
                        <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="footerPrefix" class="form-label">Copyright Text</label>
                                <input type="text" id="footerPrefix" name="footer_copyright_prefix" value="{{ $s('footer_copyright_prefix', '© 2026, made with ❤️ by') }}" maxlength="100" class="form-control">
                            </div>
                            <div>
                                <label for="footerName" class="form-label">Credit Name</label>
                                <input type="text" id="footerName" name="footer_creator_name" value="{{ $s('footer_creator_name', 'Decent Infoways') }}" maxlength="100" class="form-control">
                            </div>
                            <div>
                                <label for="footerUrl" class="form-label">Credit Link</label>
                                <input type="url" id="footerUrl" name="footer_creator_url" value="{{ $s('footer_creator_url', 'https://decentinfoways.com') }}" maxlength="255" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: Live Preview --}}
                <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Admin Preview</h3>
                            <span class="text-[11px] font-semibold text-slate-400">Live</span>
                        </div>
                        <div class="card-body">
                            <div class="pv-shell" id="themePreview">
                                <aside class="pv-side">
                                    <div class="pv-brand"><span class="pv-logo"><i class="ph-fill ph-basket"></i></span><span class="pv-brand-name" id="pvStoreName">{{ $s('store_name', 'Fresh Express') ?: 'Fresh Express' }}</span></div>
                                    <span class="pv-item"><i class="ph ph-squares-four"></i> Dashboard</span>
                                    <span class="pv-item is-active"><i class="ph-fill ph-shopping-cart-simple"></i> Orders</span>
                                    <span class="pv-item"><i class="ph ph-package"></i> Products</span>
                                    <span class="pv-item"><i class="ph ph-users"></i> Users</span>
                                </aside>
                                <div class="pv-main">
                                    <span class="pv-line w-3/4"></span>
                                    <span class="pv-line w-1/2"></span>
                                    <a class="pv-link" href="#" onclick="return false">View all orders</a>
                                    <div class="pv-btns">
                                        <span class="pv-btn pv-btn-primary" id="previewPrimaryBtn"><i class="ph ph-floppy-disk"></i> Save</span>
                                        <span class="pv-btn pv-btn-accent" id="previewAccentBtn"><i class="ph-bold ph-plus"></i> Add</span>
                                    </div>
                                    <span class="pv-input"></span>
                                </div>
                            </div>
                            <p class="form-hint mt-3">The real sidebar, headers, and buttons update automatically as you edit color hex codes.</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body flex gap-2.5">
                            <a href="{{ route('admin.settings.reset') }}" class="btn btn-outline flex-1 justify-center" data-confirm="Reset all colors to system defaults?" data-confirm-button="Reset" data-confirm-danger><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
                            <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph ph-floppy-disk"></i> Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================== TAB 2: FRONTEND WEB COLORS (LIGHT) ============================== --}}
        @if($isSuperAdmin)
        <div id="tabFrontTheme" class="tab-pane hidden space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
                <div class="xl:col-span-2 space-y-5 min-w-0">

                    {{-- Frontend Presets --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-sparkle"></i> Storefront Palette Presets</h3>
                                <p class="card-subtitle">One-click theme switch for the entire public storefront (Buttons, Cards, Header, Topbar, Badges).</p>
                            </div>
                            <span class="badge badge-primary font-bold">1-Click Apply</span>
                        </div>
                        <div class="card-body grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($frontPresets as $fp)
                                <button type="button" onclick="applyFrontendPreset(@js($fp['colors']), @js($fp['dark']))" class="group text-left p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-md transition">
                                    <span class="flex h-8 rounded-xl overflow-hidden mb-2.5 border border-black/5 shadow-inner">
                                        <span class="w-1/4" style="background: {{ $fp['colors'][0] }}"></span>
                                        <span class="w-1/4" style="background: {{ $fp['colors'][3] }}"></span>
                                        <span class="w-1/4" style="background: {{ $fp['colors'][5] }}"></span>
                                        <span class="w-1/4" style="background: {{ $fp['colors'][9] }}"></span>
                                    </span>
                                    <span class="block text-sm font-extrabold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $fp['name'] }}</span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $fp['desc'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Frontend Light Color Pickers --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-palette"></i> Frontend Web Light Mode Colors</h3>
                                <p class="card-subtitle">Manage all colors of the public website (Buttons, Navigation, Cards, Tables, Inputs, Dropdowns, Text).</p>
                            </div>
                            <span class="badge badge-success badge-dot">Light Theme</span>
                        </div>
                        <div class="card-body space-y-7">
                            @foreach($frontLightGroups as $groupName => $fields)
                                <div>
                                    <p class="flex items-center gap-2 text-[12px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-800 mb-4">
                                        <i class="ph-bold ph-paint-brush text-emerald-600"></i> {{ $groupName }}
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                                        @foreach($fields as [$key, $pickerId, $textId, $label, $hint, $default])
                                            @php $val = $s($key, $default); @endphp
                                            <div>
                                                <label for="{{ $textId }}" class="form-label font-bold text-slate-800 dark:text-slate-200">{{ $label }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" id="{{ $pickerId }}" value="{{ preg_match('/^#[0-9a-fA-F]{6}$/', $val) ? strtolower($val) : $default }}" aria-label="{{ $label }} picker"
                                                           class="w-11 h-10 shrink-0 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                                                    <input type="text" id="{{ $textId }}" name="{{ $key }}" value="{{ $val }}" maxlength="25" spellcheck="false"
                                                           class="form-control font-mono uppercase font-bold text-slate-800 dark:text-slate-100{{ $errors->has($key) ? ' !border-rose-400' : '' }}">
                                                </div>
                                                <p class="form-hint">{{ $hint }}</p>
                                                @error($key)<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Frontend Live Preview Widget --}}
                <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ph-duotone ph-laptop"></i> Storefront Preview</h3>
                            <span class="text-[11px] font-bold text-emerald-600">Light Mode</span>
                        </div>
                        <div class="card-body p-0 overflow-hidden">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-800" style="background: var(--fp-topbar-bg, #064E3B); color: var(--fp-topbar-text, #ECFDF5);">
                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span>⚡ 2-Hour Express Delivery</span>
                                    <span>English · Gujarati</span>
                                </div>
                            </div>
                            <div class="p-3.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2" style="background: var(--fp-header-bg, #FFFFFF);">
                                <div class="flex items-center gap-1.5 font-extrabold text-sm" style="color: var(--fp-brand, #059669);">
                                    <i class="ph-fill ph-basket text-lg"></i> FreshExpress
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold" style="background: var(--fp-btn-pri-bg, #059669); color: var(--fp-btn-pri-text, #FFFFFF);">
                                    <i class="ph ph-shopping-cart-simple"></i> Cart (3)
                                </span>
                            </div>
                            <div class="p-4 space-y-3" style="background: var(--fp-body-bg, #F6F8F7);">
                                <div class="p-3 rounded-2xl border" style="background: var(--fp-card-bg, #FFFFFF); border-color: var(--fp-card-border, #E2E8F0);">
                                    <span class="text-xs font-extrabold block" style="color: var(--fp-text-pri, #0F172A);">Organic Alphonso Mangoes</span>
                                    <span class="text-[11px] block mt-0.5" style="color: var(--fp-text-mut, #64748B);">1 kg box · Fresh fruit</span>
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="font-extrabold text-sm" style="color: var(--fp-brand, #059669);">₹350.00</span>
                                        <button type="button" class="px-3 py-1.5 rounded-xl font-extrabold text-xs shadow-sm" style="background: var(--fp-btn-pri-bg, #059669); color: var(--fp-btn-pri-text, #FFFFFF);">
                                            + Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 text-center text-[10px]" style="background: var(--fp-footer-bg, #0F172A); color: var(--fp-footer-text, #94A3B8);">
                                © 2026 FreshExpress Grocery Storefront
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body flex gap-2.5">
                            <a href="{{ route('admin.settings.reset') }}" class="btn btn-outline flex-1 justify-center" data-confirm="Reset all storefront and admin colors to default?" data-confirm-button="Reset" data-confirm-danger><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
                            <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph ph-floppy-disk"></i> Save Colors</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================== TAB 3: DARK MODE COLORS ============================== --}}
        <div id="tabFrontDarkTheme" class="tab-pane hidden space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
                <div class="xl:col-span-2 space-y-5 min-w-0">

                    {{-- Dark Mode Info --}}
                    <div class="card border-l-4 border-indigo-500">
                        <div class="card-body flex items-start gap-3">
                            <i class="ph-duotone ph-moon-stars text-2xl text-indigo-500 shrink-0"></i>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Independent Dark Mode Color Customization</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">When users toggle Dark Mode on the storefront, these dedicated dark surfaces, borders, text, and button tones are applied automatically.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Dark Mode Color Groups --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title"><i class="ph-duotone ph-moon"></i> Dark Mode Colors</h3>
                                <p class="card-subtitle">Fine-tune dark backgrounds, cards, inputs, buttons, and text highlights.</p>
                            </div>
                            <span class="badge badge-neutral font-bold">Dark Palette</span>
                        </div>
                        <div class="card-body space-y-7">
                            @foreach($frontDarkGroups as $groupName => $fields)
                                <div>
                                    <p class="flex items-center gap-2 text-[12px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-slate-800 mb-4">
                                        <i class="ph-bold ph-moon-stars text-indigo-500"></i> {{ $groupName }}
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                                        @foreach($fields as [$key, $pickerId, $textId, $label, $hint, $default])
                                            @php $val = $s($key, $default); @endphp
                                            <div>
                                                <label for="{{ $textId }}" class="form-label font-bold text-slate-800 dark:text-slate-200">{{ $label }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" id="{{ $pickerId }}" value="{{ preg_match('/^#[0-9a-fA-F]{6}$/', $val) ? strtolower($val) : $default }}" aria-label="{{ $label }} picker"
                                                           class="w-11 h-10 shrink-0 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                                                    <input type="text" id="{{ $textId }}" name="{{ $key }}" value="{{ $val }}" maxlength="25" spellcheck="false"
                                                           class="form-control font-mono uppercase font-bold text-slate-800 dark:text-slate-100{{ $errors->has($key) ? ' !border-rose-400' : '' }}">
                                                </div>
                                                <p class="form-hint">{{ $hint }}</p>
                                                @error($key)<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Dark Live Preview Widget --}}
                <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ph-duotone ph-moon-stars"></i> Storefront Preview</h3>
                            <span class="text-[11px] font-bold text-indigo-400">Dark Mode</span>
                        </div>
                        <div class="card-body p-0 overflow-hidden rounded-b-2xl border border-slate-800">
                            <div class="p-3 border-b border-slate-800" style="background: var(--fpd-topbar-bg, #020617); color: var(--fpd-topbar-text, #94A3B8);">
                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span>⚡ 2-Hour Express Delivery</span>
                                    <span>English · Gujarati</span>
                                </div>
                            </div>
                            <div class="p-3.5 border-b border-slate-800 flex items-center justify-between gap-2" style="background: var(--fpd-header-bg, #0B1120);">
                                <div class="flex items-center gap-1.5 font-extrabold text-sm" style="color: var(--fpd-brand, #34D399);">
                                    <i class="ph-fill ph-basket text-lg"></i> FreshExpress
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold" style="background: var(--fpd-btn-pri-bg, #059669); color: var(--fpd-btn-pri-text, #FFFFFF);">
                                    <i class="ph ph-shopping-cart-simple"></i> Cart (3)
                                </span>
                            </div>
                            <div class="p-4 space-y-3" style="background: var(--fpd-body-bg, #020617);">
                                <div class="p-3 rounded-2xl border" style="background: var(--fpd-card-bg, #0F172A); border-color: var(--fpd-card-border, #1E293B);">
                                    <span class="text-xs font-extrabold block" style="color: var(--fpd-text-pri, #F1F5F9);">Fresh Malai Paneer</span>
                                    <span class="text-[11px] block mt-0.5" style="color: var(--fpd-text-mut, #94A3B8);">200g · Dairy & Eggs</span>
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="font-extrabold text-sm" style="color: var(--fpd-brand, #34D399);">₹85.00</span>
                                        <button type="button" class="px-3 py-1.5 rounded-xl font-extrabold text-xs shadow-sm" style="background: var(--fpd-btn-pri-bg, #059669); color: var(--fpd-btn-pri-text, #FFFFFF);">
                                            + Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 text-center text-[10px]" style="background: var(--fpd-footer-bg, #020617); color: var(--fpd-footer-text, #64748B);">
                                © 2026 FreshExpress Grocery Storefront
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body flex gap-2.5">
                            <a href="{{ route('admin.settings.reset') }}" class="btn btn-outline flex-1 justify-center" data-confirm="Reset all colors to system defaults?" data-confirm-button="Reset" data-confirm-danger><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
                            <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph ph-floppy-disk"></i> Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </form>
@endsection

@push('styles')
<style>
    .tab-btn.is-active {
        border-color: var(--theme-primary, #059669) !important;
        color: var(--theme-primary, #059669) !important;
    }
    .mode-tile:has(input:checked) { border-color: #10b981; background: rgba(16,185,129,.06); }
    .dark .mode-tile:has(input:checked) { border-color: rgba(16,185,129,.55); background: rgba(16,185,129,.1); }

    .pv-shell { display: flex; height: 230px; border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 11px; }
    .dark .pv-shell { border-color: #334155; background: #0b1220; }
    .pv-side { width: 46%; padding: 10px 8px; display: flex; flex-direction: column; gap: 4px; background: var(--pv-sidebar-bg, var(--sidebar-bg)); color: var(--pv-sidebar-text, var(--sidebar-text)); }
    .pv-brand { display: flex; align-items: center; gap: 6px; padding: 2px 4px 8px; font-weight: 800; font-size: 11.5px; min-width: 0; }
    .pv-brand-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pv-logo { width: 22px; height: 22px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--pv-active, var(--sidebar-active)); color: var(--pv-active-text, var(--sidebar-active-text)); font-size: 13px; }
    .pv-item { display: flex; align-items: center; gap: 6px; padding: 6px 8px; border-radius: 8px; font-weight: 600; opacity: .78; white-space: nowrap; overflow: hidden; }
    .pv-item i { font-size: 13px; }
    .pv-item.is-active { opacity: 1; background: var(--pv-active, var(--sidebar-active)); color: var(--pv-active-text, var(--sidebar-active-text)); }
    .pv-main { flex: 1; padding: 14px 12px; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
    .pv-line { display: block; height: 8px; border-radius: 99px; background: #e2e8f0; }
    .dark .pv-line { background: #1e293b; }
    .pv-link { font-weight: 700; color: var(--pv-hover, var(--theme-hover)); text-decoration: underline; text-underline-offset: 2px; }
    .dark .pv-link { filter: brightness(1.6); }
    .pv-btns { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
    .pv-btn { display: inline-flex; align-items: center; gap: 4px; height: 28px; padding: 0 10px; border-radius: 8px; font-weight: 700; cursor: default; transition: background .15s; }
    .pv-btn-primary { background: var(--pv-btn-bg, var(--btn-primary-bg)); color: var(--pv-btn-text, var(--btn-primary-text)); }
    .pv-btn-primary:hover { background: var(--pv-btn-hover, var(--btn-primary-hover)); }
    .pv-btn-accent { background: var(--pv-accent-bg, var(--btn-accent-bg)); color: var(--pv-accent-text, var(--btn-accent-text)); }
    .pv-input { display: block; height: 26px; margin-top: auto; border-radius: 8px; background: #fff; border: 1.5px solid var(--pv-brand, var(--theme-primary)); box-shadow: 0 0 0 3px color-mix(in srgb, var(--pv-brand, var(--theme-primary)) 18%, transparent); }
    .dark .pv-input { background: #0f172a; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const root = document.documentElement;
    const HEX = /^#[0-9A-F]{6}$/i;
    const rgb = (h) => [1, 3, 5].map(i => parseInt(h.slice(i, i + 2), 16));
    const contrast = (h) => { const [r, g, b] = rgb(h); return ((0.299 * r + 0.587 * g + 0.114 * b) / 255) > 0.6 ? '#0f172a' : '#ffffff'; };

    // Tab switcher
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('is-active', 'border-emerald-600', 'text-emerald-600', 'dark:text-emerald-400');
                b.classList.add('border-transparent', 'text-slate-500');
            });
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));

            btn.classList.add('is-active', 'border-emerald-600', 'text-emerald-600', 'dark:text-emerald-400');
            btn.classList.remove('border-transparent', 'text-slate-500');

            const targetId = btn.getAttribute('data-tab-target');
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.remove('hidden');
        });
    });

    // Admin field key -> CSS variables
    const adminVars = {
        btn_primary_bg:       (v) => ({ '--pv-btn-bg': v, '--btn-primary-bg': v }),
        btn_primary_text:     (v) => ({ '--pv-btn-text': v, '--btn-primary-text': v }),
        btn_primary_hover:    (v) => ({ '--pv-btn-hover': v, '--btn-primary-hover': v }),
        btn_accent_bg:        (v) => ({ '--pv-accent-bg': v, '--btn-accent-bg': v }),
        btn_accent_text:      (v) => ({ '--pv-accent-text': v, '--btn-accent-text': v }),
        sidebar_bg_color:     (v) => ({ '--pv-sidebar-bg': v, '--sidebar-bg': v }),
        sidebar_active_color: (v) => ({ '--pv-active': v, '--pv-active-text': contrast(v), '--sidebar-active': v, '--sidebar-active-text': contrast(v) }),
        sidebar_text_color:   (v) => ({ '--pv-sidebar-text': v, '--sidebar-text': v, '--c-sidebar-text': rgb(v).join(' ') }),
        theme_primary_color:  (v) => ({ '--pv-brand': v, '--theme-primary': v, '--c-primary': rgb(v).join(' ') }),
        theme_hover_color:    (v) => ({ '--pv-hover': v, '--theme-hover': v }),
    };

    // Frontend live preview variables
    const frontVars = {
        front_brand_primary:  (v) => ({ '--fp-brand': v }),
        front_topbar_bg:      (v) => ({ '--fp-topbar-bg': v }),
        front_topbar_text:    (v) => ({ '--fp-topbar-text': v }),
        front_header_bg:      (v) => ({ '--fp-header-bg': v }),
        front_body_bg:        (v) => ({ '--fp-body-bg': v }),
        front_card_bg:        (v) => ({ '--fp-card-bg': v }),
        front_card_border:    (v) => ({ '--fp-card-border': v }),
        front_footer_bg:      (v) => ({ '--fp-footer-bg': v }),
        front_footer_text:    (v) => ({ '--fp-footer-text': v }),
        front_text_primary:   (v) => ({ '--fp-text-pri': v }),
        front_text_muted:     (v) => ({ '--fp-text-mut': v }),
        front_btn_primary_bg: (v) => ({ '--fp-btn-pri-bg': v }),
        front_btn_primary_text: (v) => ({ '--fp-btn-pri-text': v }),
        
        // Dark
        front_dark_brand_primary:  (v) => ({ '--fpd-brand': v }),
        front_dark_topbar_bg:      (v) => ({ '--fpd-topbar-bg': v }),
        front_dark_topbar_text:    (v) => ({ '--fpd-topbar-text': v }),
        front_dark_header_bg:      (v) => ({ '--fpd-header-bg': v }),
        front_dark_body_bg:        (v) => ({ '--fpd-body-bg': v }),
        front_dark_card_bg:        (v) => ({ '--fpd-card-bg': v }),
        front_dark_card_border:    (v) => ({ '--fpd-card-border': v }),
        front_dark_footer_bg:      (v) => ({ '--fpd-footer-bg': v }),
        front_dark_footer_text:    (v) => ({ '--fpd-footer-text': v }),
        front_dark_text_primary:   (v) => ({ '--fpd-text-pri': v }),
        front_dark_text_muted:     (v) => ({ '--fpd-text-mut': v }),
        front_dark_btn_primary_bg: (v) => ({ '--fpd-btn-pri-bg': v }),
        front_dark_btn_primary_text: (v) => ({ '--fpd-btn-pri-text': v }),
    };

    function preview(key, val) {
        if (!HEX.test(val)) return;
        if (adminVars[key]) {
            const map = adminVars[key](val);
            Object.keys(map).forEach(k => root.style.setProperty(k, map[k]));
        }
        if (frontVars[key]) {
            const map = frontVars[key](val);
            Object.keys(map).forEach(k => root.style.setProperty(k, map[k]));
        }
    }

    function setFieldVal(name, val) {
        const text = document.querySelector(`input[name="${name}"]`);
        if (text) {
            text.value = val.toUpperCase();
            const picker = text.parentElement.querySelector('input[type="color"]');
            if (picker) picker.value = val.toLowerCase();
            preview(name, val);
        }
    }

    window.applyAdminPreset = function (primaryBg, primaryHover, accentBg, sidebarBg, sidebarActive, themePrimary, themeHover) {
        setFieldVal('btn_primary_bg', primaryBg);
        setFieldVal('btn_primary_hover', primaryHover);
        setFieldVal('btn_accent_bg', accentBg);
        setFieldVal('sidebar_bg_color', sidebarBg);
        setFieldVal('sidebar_active_color', sidebarActive);
        setFieldVal('theme_primary_color', themePrimary);
        setFieldVal('theme_hover_color', themeHover);
        setFieldVal('btn_primary_text', '#FFFFFF');
        setFieldVal('btn_accent_text', '#FFFFFF');
        setFieldVal('sidebar_text_color', contrast(sidebarBg).toUpperCase());
        if (window.toastr) toastr.success('Admin preset applied. Save settings to make permanent.');
    };

    window.applyFrontendPreset = function (lightColors, darkColors) {
        // Light mapping: [brand_primary, brand_hover, brand_light, accent, btn_pri_bg, topbar_bg, body_bg, card_bg, card_border, footer_bg]
        setFieldVal('front_brand_primary', lightColors[0]);
        setFieldVal('front_brand_hover', lightColors[1]);
        setFieldVal('front_brand_light', lightColors[2]);
        setFieldVal('front_accent_color', lightColors[3]);
        setFieldVal('front_btn_primary_bg', lightColors[4]);
        setFieldVal('front_btn_primary_text', '#FFFFFF');
        setFieldVal('front_btn_primary_hover', lightColors[1]);
        setFieldVal('front_btn_accent_bg', lightColors[3]);
        setFieldVal('front_btn_accent_text', '#FFFFFF');
        setFieldVal('front_topbar_bg', lightColors[5]);
        setFieldVal('front_topbar_text', '#ECFDF5');
        setFieldVal('front_header_bg', '#FFFFFF');
        setFieldVal('front_body_bg', lightColors[6]);
        setFieldVal('front_card_bg', lightColors[7]);
        setFieldVal('front_card_border', lightColors[8]);
        setFieldVal('front_footer_bg', lightColors[9]);
        setFieldVal('front_footer_text', '#94A3B8');
        setFieldVal('front_text_primary', '#0F172A');
        setFieldVal('front_text_muted', '#64748B');
        setFieldVal('front_input_bg', '#FFFFFF');
        setFieldVal('front_input_border', lightColors[8]);
        setFieldVal('front_dropdown_bg', '#FFFFFF');

        // Dark mapping
        if (darkColors && darkColors.length >= 10) {
            setFieldVal('front_dark_brand_primary', darkColors[0]);
            setFieldVal('front_dark_brand_hover', darkColors[1]);
            setFieldVal('front_dark_brand_light', darkColors[2]);
            setFieldVal('front_dark_accent_color', darkColors[3]);
            setFieldVal('front_dark_btn_primary_bg', darkColors[4]);
            setFieldVal('front_dark_btn_primary_text', '#FFFFFF');
            setFieldVal('front_dark_btn_primary_hover', darkColors[0]);
            setFieldVal('front_dark_body_bg', darkColors[5]);
            setFieldVal('front_dark_card_bg', darkColors[6]);
            setFieldVal('front_dark_card_border', darkColors[7]);
            setFieldVal('front_dark_header_bg', darkColors[8]);
            setFieldVal('front_dark_topbar_bg', darkColors[5]);
            setFieldVal('front_dark_topbar_text', '#94A3B8');
            setFieldVal('front_dark_footer_bg', darkColors[5]);
            setFieldVal('front_dark_footer_text', '#64748B');
            setFieldVal('front_dark_text_primary', darkColors[9]);
            setFieldVal('front_dark_text_muted', '#94A3B8');
            setFieldVal('front_dark_input_bg', '#0B1324');
            setFieldVal('front_dark_input_border', darkColors[7]);
            setFieldVal('front_dark_dropdown_bg', darkColors[6]);
        }

        if (window.toastr) toastr.success('Frontend palette applied. Save settings to apply live.');
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#settingsForm input[type="color"]').forEach(function (picker) {
            const text = picker.parentElement.querySelector('input[type="text"]');
            if (!text) return;
            picker.addEventListener('input', function () {
                text.value = picker.value.toUpperCase();
                preview(text.name, picker.value);
            });
            text.addEventListener('input', function () {
                const v = text.value.trim();
                if (HEX.test(v)) {
                    picker.value = v.toLowerCase();
                    preview(text.name, v);
                }
            });
            if (HEX.test(text.value.trim())) preview(text.name, text.value.trim());
        });

        const storeName = document.getElementById('storeName');
        if (storeName) {
            storeName.addEventListener('input', function () {
                const v = storeName.value.trim() || 'Fresh Express';
                const pv = document.getElementById('pvStoreName');
                if (pv) pv.textContent = v;
                document.querySelectorAll('.sb-brand-name').forEach(el => { el.textContent = v; });
            });
        }
    });
})();
</script>
@endpush
