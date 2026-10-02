@extends('admin.layouts.admin')

@section('title', 'System & Theme Settings')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">System & Theme Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure global branding, footer credits, theme colors and appearance preferences.</p>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm" class="space-y-6">
        @csrf

        <!-- Brand & Footer Configuration Card (Matching Screenshot Top Section) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                <i class="fas fa-heart text-rose-500"></i>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Footer & Branding</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Text prefix -->
                <div>
                    <input type="text" name="footer_copyright_prefix" value="{{ old('footer_copyright_prefix', $settings['footer_copyright_prefix'] ?? '© 2026, made with ❤️ by') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Text displayed before the creator link.</p>
                </div>

                <!-- Creator Name -->
                <div>
                    <input type="text" name="footer_creator_name" value="{{ old('footer_creator_name', $settings['footer_creator_name'] ?? 'Decent Infoways') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">The clickable creator name in the footer.</p>
                </div>

                <!-- Creator URL -->
                <div>
                    <input type="text" name="footer_creator_url" value="{{ old('footer_creator_url', $settings['footer_creator_url'] ?? 'https://decentinfoways.com') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Website URL for the creator brand link.</p>
                </div>
            </div>
        </div>

        <!-- Theme Color Customization Card (Matching Screenshot Middle Section) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-700">
                <i class="fas fa-palette text-slate-700 dark:text-slate-300"></i>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Theme Color Customization</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Theme Primary Color -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Theme Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="primaryColorPicker" value="{{ $settings['theme_primary_color'] ?? '#000000' }}"
                               class="w-14 h-12 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="primaryColorText" name="theme_primary_color" value="{{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#000000') }}"
                               class="w-36 px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Choose the primary brand theme color used across buttons, active elements, and highlights.</p>
                </div>

                <!-- Theme Hover Color -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Theme Hover Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="hoverColorPicker" value="{{ $settings['theme_hover_color'] ?? '#a1a1a1' }}"
                               class="w-14 h-12 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="hoverColorText" name="theme_hover_color" value="{{ old('theme_hover_color', $settings['theme_hover_color'] ?? '#a1a1a1') }}"
                               class="w-36 px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Choose the theme hover/active state color used on button hover states and clickable links.</p>
                </div>

                <!-- Sidebar Background Color -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Sidebar Background Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="sidebarBgPicker" value="{{ $settings['sidebar_bg_color'] ?? '#000000' }}"
                               class="w-14 h-12 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="sidebarBgText" name="sidebar_bg_color" value="{{ old('sidebar_bg_color', $settings['sidebar_bg_color'] ?? '#000000') }}"
                               class="w-36 px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Choose the background color for the left navigation sidebar.</p>
                </div>

                <!-- Sidebar Active/Open Item Color -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Sidebar Active/Open Item Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="sidebarActivePicker" value="{{ $settings['sidebar_active_color'] ?? '#add8e6' }}"
                               class="w-14 h-12 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-1 bg-white dark:bg-slate-900">
                        <input type="text" id="sidebarActiveText" name="sidebar_active_color" value="{{ old('sidebar_active_color', $settings['sidebar_active_color'] ?? '#add8e6') }}"
                               class="w-36 px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Choose the background color for active sidebar links and opened menu category toggles.</p>
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
document.addEventListener('DOMContentLoaded', function() {
    function linkColorPickers(pickerId, textId) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (picker && text) {
            picker.addEventListener('input', function() {
                text.value = picker.value.toUpperCase();
            });
            text.addEventListener('input', function() {
                if (/^#[0-9A-F]{6}$/i.test(text.value)) {
                    picker.value = text.value;
                }
            });
        }
    }

    linkColorPickers('primaryColorPicker', 'primaryColorText');
    linkColorPickers('hoverColorPicker', 'hoverColorText');
    linkColorPickers('sidebarBgPicker', 'sidebarBgText');
    linkColorPickers('sidebarActivePicker', 'sidebarActiveText');
});
</script>
@endsection
