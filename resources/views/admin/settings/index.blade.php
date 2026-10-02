@extends('admin.layouts.admin')

@section('title', 'System & Theme Settings')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb / Header with Quick Save -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">System & Theme Customization</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Configure global branding, button colors, and set permanent default theme.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.reset') }}" onclick="return confirm('Reset all colors and branding to defaults?')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition">
                <i class="fa-solid fa-rotate-left mr-1"></i> Reset
            </a>
            <button type="submit" form="settingsForm" class="px-5 py-2.5 btn-theme-primary font-bold rounded-xl text-xs shadow-md transition active:scale-95 flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Theme as Default</span>
            </button>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm" class="space-y-6">
        @csrf

        <!-- 1-Click Quick Theme Presets -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">1-Click Professional Theme Presets</h3>
                </div>
                <span class="text-[11px] font-bold text-slate-400">Click any preset to apply instantly</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <!-- Preset 1: Midnight Slate -->
                <button type="button" onclick="applyPreset('#0F172A', '#1E293B', '#10B981', '#000000', '#ADD8E6', '#0F172A', '#334155')" class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-slate-900 dark:hover:border-white bg-slate-50 dark:bg-slate-900 text-left transition group hover:shadow-md">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-900 dark:bg-slate-700 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-sky-300 inline-block"></span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-indigo-600">Midnight Slate</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Classic Dark & Clean</p>
                </button>

                <!-- Preset 2: Emerald Green Store -->
                <button type="button" onclick="applyPreset('#059669', '#047857', '#0284C7', '#064E3B', '#6EE7B7', '#059669', '#047857')" class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 bg-emerald-50/50 dark:bg-slate-900 text-left transition group hover:shadow-md">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-sky-500 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-300 inline-block"></span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-600">Emerald Fresh</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Grocery & Organic</p>
                </button>

                <!-- Preset 3: Royal Indigo -->
                <button type="button" onclick="applyPreset('#4338CA', '#3730A3', '#06B6D4', '#1E1B4B', '#C7D2FE', '#4338CA', '#3730A3')" class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-indigo-500 bg-indigo-50/50 dark:bg-slate-900 text-left transition group hover:shadow-md">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-indigo-600 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-cyan-500 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-indigo-300 inline-block"></span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-indigo-600">Royal Indigo</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Modern Tech SaaS</p>
                </button>

                <!-- Preset 4: Luxury Violet -->
                <button type="button" onclick="applyPreset('#7C3AED', '#6D28D9', '#EC4899', '#0F172A', '#E9D5FF', '#7C3AED', '#6D28D9')" class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-purple-500 bg-purple-50/50 dark:bg-slate-900 text-left transition group hover:shadow-md">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-purple-600 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-pink-500 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-purple-300 inline-block"></span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-purple-600">Luxury Violet</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Premium & Royal</p>
                </button>

                <!-- Preset 5: Amber Gold -->
                <button type="button" onclick="applyPreset('#D97706', '#B45309', '#EF4444', '#18181B', '#FDE68A', '#D97706', '#B45309')" class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-amber-500 bg-amber-50/50 dark:bg-slate-900 text-left transition group hover:shadow-md">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-amber-600 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-500 inline-block"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-amber-300 inline-block"></span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-amber-600">Amber Gold</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Warm E-commerce</p>
                </button>
            </div>
        </div>

        <!-- Theme Color & Button Customization Card -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <i class="fas fa-palette text-slate-700 dark:text-slate-300"></i>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Custom Color Palette</h2>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-lg">Live Sync</span>
            </div>

            <!-- Live Button Preview Strip -->
            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Live Preview of Buttons & Theme</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">See how your buttons and highlights look in real-time as you pick colors.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="previewPrimaryBtn" class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition" style="background-color: {{ $settings['btn_primary_bg'] ?? '#0f172a' }}; color: {{ $settings['btn_primary_text'] ?? '#ffffff' }};">
                        <i class="fas fa-floppy-disk mr-1.5"></i> Primary Button
                    </button>
                    <button type="button" id="previewAccentBtn" class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition" style="background-color: {{ $settings['btn_accent_bg'] ?? '#10b981' }}; color: {{ $settings['btn_accent_text'] ?? '#ffffff' }};">
                        <i class="fas fa-plus mr-1.5"></i> Accent Action
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Button Primary Background Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Button Primary Background</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="btnPrimaryBgPicker" value="{{ $settings['btn_primary_bg'] ?? '#0f172a' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="btnPrimaryBgText" name="btn_primary_bg" value="{{ old('btn_primary_bg', $settings['btn_primary_bg'] ?? '#0f172a') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Background for Save, Submit, and primary call-to-action buttons.</p>
                </div>

                <!-- Button Primary Text Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Button Text Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="btnPrimaryTextPicker" value="{{ $settings['btn_primary_text'] ?? '#ffffff' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="btnPrimaryTextText" name="btn_primary_text" value="{{ old('btn_primary_text', $settings['btn_primary_text'] ?? '#ffffff') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Text and icon color inside primary buttons.</p>
                </div>

                <!-- Button Primary Hover Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Button Hover Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="btnPrimaryHoverPicker" value="{{ $settings['btn_primary_hover'] ?? '#1e293b' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="btnPrimaryHoverText" name="btn_primary_hover" value="{{ old('btn_primary_hover', $settings['btn_primary_hover'] ?? '#1e293b') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Background color when hovering over buttons.</p>
                </div>

                <!-- Button Accent Background Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Accent / Action Button Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="btnAccentBgPicker" value="{{ $settings['btn_accent_bg'] ?? '#10b981' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="btnAccentBgText" name="btn_accent_bg" value="{{ old('btn_accent_bg', $settings['btn_accent_bg'] ?? '#10b981') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Background color for "Add New", "+ Create", and success buttons.</p>
                </div>

                <!-- Button Accent Text Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Accent Button Text Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="btnAccentTextPicker" value="{{ $settings['btn_accent_text'] ?? '#ffffff' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="btnAccentTextText" name="btn_accent_text" value="{{ old('btn_accent_text', $settings['btn_accent_text'] ?? '#ffffff') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Text and icon color inside accent buttons.</p>
                </div>

                <!-- Sidebar Background Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Sidebar Background Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="sidebarBgPicker" value="{{ $settings['sidebar_bg_color'] ?? '#000000' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="sidebarBgText" name="sidebar_bg_color" value="{{ old('sidebar_bg_color', $settings['sidebar_bg_color'] ?? '#000000') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Background color for the left navigation sidebar.</p>
                </div>

                <!-- Sidebar Active Item Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Sidebar Active Item Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="sidebarActivePicker" value="{{ $settings['sidebar_active_color'] ?? '#add8e6' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="sidebarActiveText" name="sidebar_active_color" value="{{ old('sidebar_active_color', $settings['sidebar_active_color'] ?? '#add8e6') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Background color for active sidebar links.</p>
                </div>

                <!-- Theme Primary Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Theme Brand Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="primaryColorPicker" value="{{ $settings['theme_primary_color'] ?? '#0f172a' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="primaryColorText" name="theme_primary_color" value="{{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#0f172a') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">General theme brand tone across UI highlights.</p>
                </div>

                <!-- Theme Hover Color -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-2">Theme Hover / Link Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="hoverColorPicker" value="{{ $settings['theme_hover_color'] ?? '#334155' }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="hoverColorText" name="theme_hover_color" value="{{ old('theme_hover_color', $settings['theme_hover_color'] ?? '#334155') }}"
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Hover color for links and subtle interactive states.</p>
                </div>
            </div>
        </div>

        <!-- Appearance Mode (Light, Dark, System / Match PC) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                <i class="fas fa-desktop text-slate-700 dark:text-slate-300"></i>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Theme Mode (Light / Dark / System OS)</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="flex items-center gap-3 p-4 border rounded-2xl cursor-pointer transition {{ ($settings['theme_mode'] ?? 'system') === 'light' ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" name="theme_mode" value="light" {{ ($settings['theme_mode'] ?? 'system') === 'light' ? 'checked' : '' }} class="text-primary focus:ring-primary">
                    <div>
                        <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                            <i class="fas fa-sun text-amber-500"></i>
                            <span>Light Mode</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Clean crisp white background</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 border rounded-2xl cursor-pointer transition {{ ($settings['theme_mode'] ?? 'system') === 'dark' ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" name="theme_mode" value="dark" {{ ($settings['theme_mode'] ?? 'system') === 'dark' ? 'checked' : '' }} class="text-primary focus:ring-primary">
                    <div>
                        <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                            <i class="fas fa-moon text-indigo-400"></i>
                            <span>Dark Mode</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Sleek dark night theme</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 border rounded-2xl cursor-pointer transition {{ ($settings['theme_mode'] ?? 'system') === 'system' ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" name="theme_mode" value="system" {{ ($settings['theme_mode'] ?? 'system') === 'system' ? 'checked' : '' }} class="text-primary focus:ring-primary">
                    <div>
                        <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                            <i class="fas fa-laptop text-slate-500"></i>
                            <span>System (Match PC)</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Auto sync with Windows/OS theme</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Action Buttons Footer (Matching Screenshot Bottom Strip) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-end gap-3">
            <a href="{{ route('admin.settings.reset') }}" onclick="return confirm('Are you sure you want to reset all theme settings to defaults?')"
               class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-sm transition active:scale-95">
                Reset
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-black hover:bg-slate-900 text-white dark:bg-white dark:text-black dark:hover:bg-slate-200 font-semibold rounded-xl text-sm shadow-md transition active:scale-95">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
window.applyPreset = function(primaryBg, primaryHover, accentBg, sidebarBg, sidebarActive, themePrimary, themeHover) {
    function setVal(pickerId, textId, val) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (picker) picker.value = val;
        if (text) text.value = val;
    }

    setVal('btnPrimaryBgPicker', 'btnPrimaryBgText', primaryBg);
    setVal('btnPrimaryHoverPicker', 'btnPrimaryHoverText', primaryHover);
    setVal('btnAccentBgPicker', 'btnAccentBgText', accentBg);
    setVal('sidebarBgPicker', 'sidebarBgText', sidebarBg);
    setVal('sidebarActivePicker', 'sidebarActiveText', sidebarActive);
    setVal('primaryColorPicker', 'primaryColorText', themePrimary);
    setVal('hoverColorPicker', 'hoverColorText', themeHover);

    const previewPrimary = document.getElementById('previewPrimaryBtn');
    const previewAccent = document.getElementById('previewAccentBtn');
    if (previewPrimary) {
        previewPrimary.style.backgroundColor = primaryBg;
        previewPrimary.style.color = '#FFFFFF';
    }
    if (previewAccent) {
        previewAccent.style.backgroundColor = accentBg;
        previewAccent.style.color = '#FFFFFF';
    }

    if (window.toastr) {
        toastr.success('Theme preset loaded! Click "Save Theme as Default" to make it permanent.');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    function linkColorPickers(pickerId, textId, callback) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (picker && text) {
            picker.addEventListener('input', function() {
                text.value = picker.value.toUpperCase();
                if (callback) callback(picker.value);
            });
            text.addEventListener('input', function() {
                if (/^#[0-9A-F]{6}$/i.test(text.value)) {
                    picker.value = text.value;
                    if (callback) callback(text.value);
                }
            });
        }
    }

    const previewPrimary = document.getElementById('previewPrimaryBtn');
    const previewAccent = document.getElementById('previewAccentBtn');

    linkColorPickers('btnPrimaryBgPicker', 'btnPrimaryBgText', function(val) {
        if (previewPrimary) previewPrimary.style.backgroundColor = val;
    });
    linkColorPickers('btnPrimaryTextPicker', 'btnPrimaryTextText', function(val) {
        if (previewPrimary) previewPrimary.style.color = val;
    });
    linkColorPickers('btnPrimaryHoverPicker', 'btnPrimaryHoverText');
    linkColorPickers('btnAccentBgPicker', 'btnAccentBgText', function(val) {
        if (previewAccent) previewAccent.style.backgroundColor = val;
    });
    linkColorPickers('btnAccentTextPicker', 'btnAccentTextText', function(val) {
        if (previewAccent) previewAccent.style.color = val;
    });
    linkColorPickers('primaryColorPicker', 'primaryColorText');
    linkColorPickers('hoverColorPicker', 'hoverColorText');
    linkColorPickers('sidebarBgPicker', 'sidebarBgText');
    linkColorPickers('sidebarActivePicker', 'sidebarActiveText');
});
</script>
@endsection
