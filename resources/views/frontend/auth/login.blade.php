@extends('frontend.layouts.app')

@section('title', __('auth_ui.tab_login') . ' - ' . __('messages.store_name'))

@section('content')
@php $err = $errors->login; @endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-14">
    <div class="fx-card overflow-hidden grid md:grid-cols-2 shadow-[0_24px_60px_-30px_rgba(5,150,105,.35)]">
        @include('frontend.auth._panel', ['variant' => 'login'])

        <div class="p-6 sm:p-10">
            @include('frontend.auth._tabs', ['active' => 'login'])

            <div class="mt-7 flex items-center gap-3.5">
                <span class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-[1.65rem] shrink-0"><i class="ph-duotone ph-device-mobile"></i></span>
                <div class="min-w-0">
                    <h1 class="text-[1.4rem] sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">{{ __('auth_ui.login_title') }}</h1>
                    <p class="mt-0.5 text-[13px] text-slate-500 dark:text-slate-400">{{ __('auth_ui.login_sub', ['length' => $otpLength]) }}</p>
                </div>
            </div>

            <form action="{{ route('customer.otp.send') }}" method="POST" class="mt-6 space-y-4" data-loading novalidate>
                @csrf
                <div>
                    <label for="loginPhone" class="fx-label">{{ __('auth_ui.mobile') }}</label>
                    <div class="relative">
                        <span class="absolute left-0 inset-y-0 pl-4 pr-3 flex items-center gap-1.5 text-[14px] font-bold text-slate-500 border-r border-slate-200 dark:border-slate-700 my-2.5">+91</span>
                        <input type="tel" id="loginPhone" name="phone" value="{{ $phone }}" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" autocomplete="tel-national" placeholder="98765 43210" autofocus
                               oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                               @if($err->has('phone')) aria-invalid="true" aria-describedby="loginPhoneErr" @endif
                               class="fx-input !h-12 !pl-[4.5rem] !text-base font-mono font-bold tracking-wider {{ $err->has('phone') ? 'is-invalid' : '' }}">
                    </div>
                    @if($err->has('phone'))<p id="loginPhoneErr" class="text-xs font-semibold text-rose-600 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $err->first('phone') }}</p>@endif
                </div>

                @if($demoCode)
                    <button type="button" onclick="var i=document.getElementById('loginPhone');i.value='9988776655';i.focus();" class="w-full text-left flex items-start gap-2.5 p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-[12px] text-amber-900 dark:text-amber-200 hover:bg-amber-100/70 dark:hover:bg-amber-500/15 transition-colors">
                        <i class="ph-fill ph-info text-amber-500 text-base shrink-0"></i>
                        <span>{{ __('auth_ui.demo_customer') }}: <strong class="font-mono">9988776655</strong> · OTP <strong class="font-mono">{{ $demoCode }}</strong></span>
                    </button>
                @endif

                <button type="submit" class="fx-btn fx-btn-primary fx-btn-lg w-full !h-12 relative">
                    <span>{{ __('auth_ui.send_code') }}</span><i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>

            <p class="mt-5 text-center text-[13px] text-slate-500 dark:text-slate-400">
                {{ __('auth_ui.no_account') }}
                <a href="{{ route('register') }}" class="font-bold text-brand-700 dark:text-brand-400 hover:underline">{{ __('auth_ui.create_account') }}</a>
            </p>

            <p class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 text-center text-[12px] text-slate-400">
                {!! __('auth_ui.continue_agree', [
                    'terms' => '<a href="' . e(route('pages.show', 'legal-information')) . '" class="underline font-semibold text-slate-600 dark:text-slate-300">' . e(__('messages.terms')) . '</a>',
                    'privacy' => '<a href="' . e(route('pages.show', 'privacy-policy')) . '" class="underline font-semibold text-slate-600 dark:text-slate-300">' . e(__('messages.privacy_policy')) . '</a>',
                ]) !!}
            </p>
        </div>
    </div>
</div>
@endsection
