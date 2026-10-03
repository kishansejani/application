@extends('frontend.layouts.auth')

@section('title', __('auth_ui.verify_title'))

@section('panel_kicker', __('auth_ui.forgot_kicker'))

@section('panel')
    @include('auth._panel', ['step' => 2])
@endsection

@section('content')
    @include('auth._steps', ['step' => 2])

    @include('frontend.auth._otp-verify', [
        'action' => route('password.verify.post'),
        'resendAction' => route('password.otp.resend'),
        'channel' => $channel,
        'target' => $maskedTarget,
        'changeUrl' => route('password.forgot'),
        'changeLabel' => __('auth_ui.change_target'),
        'length' => $otpLength,
        'resendIn' => $resendIn,
        'resendSeconds' => $resendSeconds,
        'expiryMinutes' => $expiryMinutes,
        'demoCode' => $demoCode,
        'notice' => session('success'),
        'error' => session('otp_error') ?: ($errors->first('otp') ?: null),
    ])
@endsection
