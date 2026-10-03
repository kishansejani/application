@extends('frontend.layouts.auth')

@section('title', __('auth_ui.forgot_title'))

@section('panel_kicker', __('auth_ui.forgot_kicker'))

@section('panel')
    @include('auth._panel', ['step' => 1])
@endsection

@section('content')
    @include('auth._steps', ['step' => 1])
    <span class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl"><i class="ph-duotone ph-key"></i></span>
    <h2 class="mt-5 text-2xl sm:text-[1.75rem] font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('auth_ui.forgot_title') }}</h2>
    <p class="mt-1.5 text-[14px] text-slate-500 dark:text-slate-400">{{ __('auth_ui.forgot_sub') }}</p>

    @if(session('error'))
        <div role="alert" class="mt-6 flex items-start gap-3 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-200 text-[13px] font-semibold">
            <i class="ph-fill ph-warning-circle text-rose-500 text-lg shrink-0"></i><span>{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('password.otp.send') }}" method="POST" class="mt-6 space-y-4" novalidate>
        @csrf
        <div>
            <label for="email_or_phone" class="fx-label">{{ __('auth_ui.email_or_phone') }} <span class="text-rose-500">*</span></label>
            <div class="relative">
                <i class="ph ph-user-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none" data-target-icon></i>
                <input type="text" id="email_or_phone" name="email_or_phone" value="{{ old('email_or_phone') }}" required autofocus autocomplete="username" maxlength="150"
                       placeholder="{{ __('auth_ui.email_or_phone_ph') }}" class="fx-input !h-12 !pl-11 @error('email_or_phone') is-invalid @enderror"
                       @error('email_or_phone') aria-invalid="true" aria-describedby="eopErr" @enderror>
            </div>
            @error('email_or_phone')<p id="eopErr" class="text-xs font-semibold text-rose-600 mt-1.5 flex items-start gap-1"><i class="ph-fill ph-warning-circle mt-px"></i><span>{{ $message }}</span></p>@enderror
        </div>
        <button type="submit" class="fx-btn fx-btn-primary fx-btn-lg w-full !h-12 relative"><span>{{ __('auth_ui.send_reset_code') }}</span><i class="ph-bold ph-paper-plane-tilt"></i></button>
    </form>

    <a href="{{ route('admin.login') }}" class="mt-8 inline-flex items-center gap-1.5 text-[13px] font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white"><i class="ph-bold ph-arrow-left"></i>{{ __('auth_ui.back_to_sign_in') }}</a>
@endsection

@push('scripts')
<script>
    // icon follows what is being typed: envelope for e-mail, phone for digits
    (function () {
        var input = document.getElementById('email_or_phone'), icon = document.querySelector('[data-target-icon]');
        function sync() {
            var v = input.value.trim();
            icon.className = 'ph absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none ' +
                (v.indexOf('@') > -1 || /[a-z]/i.test(v) ? 'ph-envelope-simple' : (/^\+?\d[\d\s]*$/.test(v) ? 'ph-device-mobile' : 'ph-user-circle'));
        }
        input.addEventListener('input', sync); sync();
    })();
</script>
@endpush
