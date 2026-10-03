@extends('frontend.layouts.auth')

@section('title', __('auth_ui.reset_title'))

@section('panel_kicker', __('auth_ui.forgot_kicker'))

@section('panel')
    @include('auth._panel', ['step' => 3])
@endsection

@section('content')
    @include('auth._steps', ['step' => 3])
    <span class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl"><i class="ph-duotone ph-lock-key-open"></i></span>
    <h2 class="mt-5 text-2xl sm:text-[1.75rem] font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('auth_ui.reset_title') }}</h2>
    <p class="mt-1.5 text-[14px] text-slate-500 dark:text-slate-400">{{ __('auth_ui.reset_sub') }}</p>

    @if(session('success'))
        <div role="status" class="mt-6 flex items-start gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-200 text-[13px] font-semibold">
            <i class="ph-fill ph-check-circle text-emerald-500 text-lg shrink-0"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('password.reset.post') }}" method="POST" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="password" class="fx-label">{{ __('auth_ui.new_password') }} <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="ph ph-lock-simple absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                <input type="password" id="password" name="password" required autofocus minlength="8" maxlength="100" autocomplete="new-password" placeholder="{{ __('auth_ui.new_password_ph') }}"
                       class="fx-input !h-12 !pl-11 !pr-12 @error('password') is-invalid @enderror">
                <button type="button" data-toggle-password="password" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center" aria-label="{{ __('auth_ui.show_password') }}" aria-pressed="false"><i class="ph ph-eye text-lg"></i></button>
            </div>
            <div class="mt-2 grid grid-cols-4 gap-1" aria-hidden="true">
                @for($i = 0; $i < 4; $i++)<span data-strength-bar class="h-1 rounded-full bg-slate-200 dark:bg-slate-700 transition-colors"></span>@endfor
            </div>
            <p id="strengthText" class="text-[11px] font-semibold text-slate-400 mt-1">{{ __('auth_ui.strength_hint') }}</p>
            @error('password')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="fx-label">{{ __('auth_ui.confirm_password') }} <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="ph ph-checks absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="{{ __('auth_ui.confirm_password_ph') }}" class="fx-input !h-12 !pl-11 !pr-12">
                <button type="button" data-toggle-password="password_confirmation" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center" aria-label="{{ __('auth_ui.show_password') }}" aria-pressed="false"><i class="ph ph-eye text-lg"></i></button>
            </div>
            <p id="matchText" class="hidden text-[11px] font-semibold mt-1"></p>
        </div>
        <p class="flex items-start gap-2 text-[12px] text-slate-500 dark:text-slate-400"><i class="ph-fill ph-devices text-slate-400 text-base shrink-0"></i>{{ __('auth_ui.other_sessions_note') }}</p>
        <button type="submit" class="fx-btn fx-btn-primary fx-btn-lg w-full !h-12 relative"><i class="ph-bold ph-floppy-disk"></i><span>{{ __('auth_ui.update_password') }}</span></button>
    </form>
@endsection

@push('scripts')
@php
    $pwI18n = [
        'hint' => __('auth_ui.strength_hint'),
        'strength' => __('auth_ui.strength'),
        'labels' => __('auth_ui.strength_labels'),
        'match' => __('auth_ui.pw_match'),
        'noMatch' => __('auth_ui.pw_no_match'),
    ];
@endphp
<script>
    (function () {
        var T = @json($pwI18n);
        var pw = document.getElementById('password'), cf = document.getElementById('password_confirmation');
        var bars = document.querySelectorAll('[data-strength-bar]'), txt = document.getElementById('strengthText'), match = document.getElementById('matchText');
        var tones = ['bg-rose-500', 'bg-amber-500', 'bg-lime-500', 'bg-emerald-500'];
        function score(v) { var s = 0; if (v.length >= 8) s++; if (v.length >= 12) s++; if (/[0-9]/.test(v) && /[a-zA-Z]/.test(v)) s++; if (/[^a-zA-Z0-9]/.test(v) || /[A-Z]/.test(v) && /[a-z]/.test(v)) s++; return v ? Math.max(1, s) : 0; }
        function update() {
            var s = score(pw.value);
            bars.forEach(function (b, i) { tones.forEach(function (t) { b.classList.remove(t); }); b.classList.toggle('bg-slate-200', i >= s); b.classList.toggle('dark:bg-slate-700', i >= s); if (i < s) b.classList.add(tones[s - 1]); });
            txt.textContent = s ? T.strength + ': ' + T.labels[s - 1] : T.hint;
            if (cf.value) {
                var ok = cf.value === pw.value;
                match.classList.remove('hidden', 'text-rose-600', 'text-emerald-600');
                match.classList.add(ok ? 'text-emerald-600' : 'text-rose-600');
                match.textContent = ok ? T.match : T.noMatch;
            } else { match.classList.add('hidden'); }
        }
        pw.addEventListener('input', update); cf.addEventListener('input', update);
    })();
</script>
@endpush
