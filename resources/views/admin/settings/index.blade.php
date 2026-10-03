@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('content')
@php
    $s = fn ($key, $default) => old($key, $settings[$key] ?? $default);
    $colorGroups = [
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
    $groupIcons = ['Buttons' => 'cursor-click', 'Sidebar' => 'sidebar-simple', 'Brand' => 'paint-brush-broad'];
    $presets = [
        ['Midnight slate', 'Classic dark and clean', ['#0F172A', '#1E293B', '#10B981', '#000000', '#ADD8E6', '#0F172A', '#334155']],
        ['Emerald fresh',  'Grocery and organic',    ['#059669', '#047857', '#0284C7', '#064E3B', '#6EE7B7', '#059669', '#047857']],
        ['Royal indigo',   'Modern tech',            ['#4338CA', '#3730A3', '#06B6D4', '#1E1B4B', '#C7D2FE', '#4338CA', '#3730A3']],
        ['Luxury violet',  'Premium and bold',       ['#7C3AED', '#6D28D9', '#EC4899', '#0F172A', '#E9D5FF', '#7C3AED', '#6D28D9']],
        ['Amber gold',     'Warm e-commerce',        ['#D97706', '#B45309', '#EF4444', '#18181B', '#FDE68A', '#D97706', '#B45309']],
    ];
    $mode = old('theme_mode', $settings['theme_mode'] ?? 'system');
@endphp

    <x-admin.page-header title="Settings" subtitle="Branding, panel colours and the default appearance for all administrators." icon="gear-six">
        <a href="{{ route('admin.settings.reset') }}" class="btn btn-outline" data-confirm="All colours, footer text and the store name will return to their default values." data-confirm-title="Reset settings to defaults?" data-confirm-button="Reset" data-confirm-danger><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
        <button type="submit" form="settingsForm" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Save settings</button>
    </x-admin.page-header>

    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
            <div class="xl:col-span-2 space-y-5 min-w-0">

                {{-- General --}}
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title"><i class="ph-duotone ph-storefront"></i> General</h3>
                            <p class="card-subtitle">Shown in the sidebar, browser tab and exports.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <label for="storeName" class="form-label">Store / panel name</label>
                        <input type="text" id="storeName" name="store_name" value="{{ $s('store_name', 'Fresh Express') }}" maxlength="60" placeholder="Fresh Express" class="form-control{{ $errors->has('store_name') ? ' !border-rose-400' : '' }}">
                        <p class="form-hint">Leave blank to use the default name, Fresh Express.</p>
                        @error('store_name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Presets --}}
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title"><i class="ph-duotone ph-swatches"></i> Theme presets</h3>
                            <p class="card-subtitle">Apply a curated palette in one click, then fine-tune below.</p>
                        </div>
                    </div>
                    <div class="card-body grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach($presets as [$pName, $pHint, $c])
                            <button type="button" onclick="applyPreset('{{ implode("', '", $c) }}')" class="group text-left p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 hover:border-slate-400 dark:hover:border-slate-500 hover:shadow-soft transition {{ $loop->last ? 'col-span-2 sm:col-span-1' : '' }}">
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

                {{-- Colours --}}
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title"><i class="ph-duotone ph-palette"></i> Colour palette</h3>
                            <p class="card-subtitle">Changes preview live on this page. Save to apply them for everyone.</p>
                        </div>
                        <span class="badge badge-success badge-dot">Live preview</span>
                    </div>
                    <div class="card-body space-y-6">
                        @foreach($colorGroups as $groupName => $fields)
                            <div>
                                <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3"><i class="ph ph-{{ $groupIcons[$groupName] }} text-sm"></i> {{ $groupName }}</p>
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
                            <h3 class="card-title"><i class="ph-duotone ph-monitor"></i> Default appearance</h3>
                            <p class="card-subtitle">Used by administrators who have not picked a mode from the top bar.</p>
                        </div>
                    </div>
                    <div class="card-body grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach([['light', 'Light', 'sun', 'Bright, high-contrast surfaces'], ['dark', 'Dark', 'moon', 'Easy on the eyes at night'], ['system', 'System', 'desktop', 'Follows the device setting']] as [$mVal, $mLabel, $mIcon, $mHint])
                            <label class="mode-tile flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer transition">
                                <input type="radio" name="theme_mode" value="{{ $mVal }}" {{ $mode === $mVal ? 'checked' : '' }} class="mt-1 w-4 h-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" data-theme-radio>
                                <span>
                                    <span class="flex items-center gap-1.5 text-[13px] font-bold text-slate-900 dark:text-white"><i class="ph-duotone ph-{{ $mIcon }} text-base"></i> {{ $mLabel }}</span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $mHint }}</span>
                                </span>
                            </label>
                        @endforeach
                        @error('theme_mode')<p class="text-xs font-semibold text-rose-600 mt-1 sm:col-span-3">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title"><i class="ph-duotone ph-copyright"></i> Footer credit</h3>
                            <p class="card-subtitle">Text shown at the bottom of every admin page.</p>
                        </div>
                    </div>
                    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="footerPrefix" class="form-label">Copyright text</label>
                            <input type="text" id="footerPrefix" name="footer_copyright_prefix" value="{{ $s('footer_copyright_prefix', '© 2026, made with ❤️ by') }}" maxlength="100" class="form-control{{ $errors->has('footer_copyright_prefix') ? ' !border-rose-400' : '' }}">
                            @error('footer_copyright_prefix')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="footerName" class="form-label">Credit name</label>
                            <input type="text" id="footerName" name="footer_creator_name" value="{{ $s('footer_creator_name', 'Decent Infoways') }}" maxlength="100" class="form-control{{ $errors->has('footer_creator_name') ? ' !border-rose-400' : '' }}">
                            @error('footer_creator_name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="footerUrl" class="form-label">Credit link</label>
                            <input type="url" id="footerUrl" name="footer_creator_url" value="{{ $s('footer_creator_url', 'https://decentinfoways.com') }}" maxlength="255" class="form-control{{ $errors->has('footer_creator_url') ? ' !border-rose-400' : '' }}">
                            @error('footer_creator_url')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: live preview + actions --}}
            <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Preview</h3>
                        <span class="text-[11px] font-semibold text-slate-400">Updates as you edit</span>
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
                        <p class="form-hint mt-3">The real sidebar and buttons on this page also update while you edit. Leaving without saving discards the preview.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body flex gap-2.5">
                        <a href="{{ route('admin.settings.reset') }}" class="btn btn-outline flex-1 justify-center" data-confirm="All colours, footer text and the store name will return to their default values." data-confirm-title="Reset settings to defaults?" data-confirm-button="Reset" data-confirm-danger><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
                        <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph ph-floppy-disk"></i> Save settings</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
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

    // field key -> CSS variables to update (preview card + real page)
    const vars = {
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

    function preview(key, val) {
        if (!HEX.test(val) || !vars[key]) return;
        const map = vars[key](val);
        Object.keys(map).forEach(k => root.style.setProperty(k, map[k]));
    }

    function setVal(pickerId, textId, val) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (picker) picker.value = val.toLowerCase();
        if (text) { text.value = val.toUpperCase(); preview(text.name, val); }
    }

    // Kept global: used by the preset buttons (same signature as before).
    window.applyPreset = function (primaryBg, primaryHover, accentBg, sidebarBg, sidebarActive, themePrimary, themeHover) {
        setVal('btnPrimaryBgPicker', 'btnPrimaryBgText', primaryBg);
        setVal('btnPrimaryHoverPicker', 'btnPrimaryHoverText', primaryHover);
        setVal('btnAccentBgPicker', 'btnAccentBgText', accentBg);
        setVal('sidebarBgPicker', 'sidebarBgText', sidebarBg);
        setVal('sidebarActivePicker', 'sidebarActiveText', sidebarActive);
        setVal('primaryColorPicker', 'primaryColorText', themePrimary);
        setVal('hoverColorPicker', 'hoverColorText', themeHover);
        setVal('btnPrimaryTextPicker', 'btnPrimaryTextText', '#FFFFFF');
        setVal('btnAccentTextPicker', 'btnAccentTextText', '#FFFFFF');
        setVal('sidebarTextPicker', 'sidebarTextText', contrast(sidebarBg).toUpperCase());
        if (window.toastr) toastr.success('Preset applied. Save settings to make it permanent.');
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#settingsForm input[type="color"]').forEach(function (picker) {
            const text = picker.parentElement.querySelector('input[type="text"]');
            if (!text) return;
            picker.addEventListener('input', function () { text.value = picker.value.toUpperCase(); preview(text.name, picker.value); });
            text.addEventListener('input', function () {
                const v = text.value.trim();
                if (HEX.test(v)) { picker.value = v.toLowerCase(); preview(text.name, v); }
            });
            if (HEX.test(text.value.trim())) preview(text.name, text.value.trim());
        });

        const storeName = document.getElementById('storeName');
        storeName.addEventListener('input', function () {
            const v = storeName.value.trim() || 'Fresh Express';
            document.getElementById('pvStoreName').textContent = v;
            document.querySelectorAll('.sb-brand-name').forEach(el => { el.textContent = v; });
        });

        document.querySelectorAll('[data-theme-radio]').forEach(function (r) {
            r.addEventListener('change', function () { if (r.checked && typeof window.setThemeMode === 'function') window.setThemeMode(r.value); });
        });
    });
})();
</script>
@endpush
