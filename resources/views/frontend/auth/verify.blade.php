@extends('frontend.layouts.app')

@section('title', __('auth_ui.verify_title') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-lg mx-auto px-4 sm:px-6 py-8 sm:py-16 relative">
    {{-- Outer Ambient Glow --}}
    <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-80 h-80 bg-gradient-to-tr from-emerald-500/20 via-teal-500/15 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <div class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-[0_24px_60px_-15px_rgba(5,150,105,0.22)] border border-slate-200/80 dark:border-slate-800 p-6 sm:p-10 overflow-hidden">
        {{-- Decorative Background Watermark --}}
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-[32rem] h-[18rem] rounded-full bg-emerald-400/10 dark:bg-emerald-400/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10">
            @include('frontend.auth._otp-verify', [
                'action' => route('auth.verify'),
                'resendAction' => route('auth.otp.resend'),
                'channel' => 'sms',
                'target' => '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5),
                'changeUrl' => $changeUrl,
                'changeLabel' => __('auth_ui.change_number'),
                'length' => $otpLength,
                'resendIn' => $resendIn,
                'resendSeconds' => $resendSeconds,
                'expiryMinutes' => $expiryMinutes,
                'demoCode' => $demoCode,
                'notice' => session('otp_notice'),
                'error' => session('otp_error'),
            ])
        </div>
    </div>
</div>
@endsection
