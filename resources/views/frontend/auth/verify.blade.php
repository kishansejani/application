@extends('frontend.layouts.app')

@section('title', __('auth_ui.verify_title') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-6 sm:py-12">
    <div class="fx-card relative overflow-hidden p-6 sm:p-9 shadow-[0_24px_60px_-30px_rgba(5,150,105,.35)]">
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-[28rem] h-[16rem] rounded-full bg-brand-400/10 dark:bg-brand-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="relative">
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
