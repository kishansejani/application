@extends('frontend.layouts.app')

@section('title', __('auth_ui.tab_login') . ' - ' . __('messages.store_name'))

@section('content')
@php $err = $errors->login; @endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 relative">
    {{-- Outer Ambient Glow --}}
    <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-3/4 h-80 bg-gradient-to-tr from-emerald-500/15 via-teal-500/10 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <div class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-[0_24px_60px_-15px_rgba(5,150,105,0.18)] border border-slate-200/80 dark:border-slate-800 overflow-hidden grid md:grid-cols-2">
        {{-- Left Brand Showcase Panel --}}
        @include('frontend.auth._panel', ['variant' => 'login'])

        {{-- Right Form Section --}}
        <div class="p-6 sm:p-10 flex flex-col justify-between">
            <div>
                @include('frontend.auth._tabs', ['active' => 'login'])

                <div class="mt-8 flex items-center gap-4">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-emerald-500/15 to-teal-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-2xl shrink-0 shadow-sm">
                        <i class="ph-duotone ph-device-mobile-camera animate-pulse" style="animation-duration: 3s;"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 mb-1">
                            <i class="ph-fill ph-shield-check text-xs"></i> 100% Passwordless
                        </span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                            {{ __('auth_ui.login_title') }}
                        </h1>
                        <p class="mt-0.5 text-[13px] text-slate-500 dark:text-slate-400">
                            {{ __('auth_ui.login_sub', ['length' => $otpLength]) }}
                        </p>
                    </div>
                </div>

                <form action="{{ route('customer.otp.send') }}" method="POST" class="mt-7 space-y-4" data-loading novalidate id="loginOtpForm">
                    @csrf
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="loginPhone" class="text-[13px] font-bold text-slate-700 dark:text-slate-200">
                                {{ __('auth_ui.mobile') }} <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] font-medium text-slate-400">10 digits Indian mobile</span>
                        </div>
                        <div class="relative group/input">
                            <span class="absolute left-0 inset-y-0 pl-3.5 pr-3 flex items-center gap-1.5 text-[13.5px] font-extrabold text-slate-600 dark:text-slate-300 border-r border-slate-200 dark:border-slate-700 my-2 select-none bg-slate-50/50 dark:bg-slate-800/50 rounded-l-xl">
                                <span class="text-base" role="img" aria-label="India flag">🇮🇳</span> +91
                            </span>
                            <input type="tel" id="loginPhone" name="phone" value="{{ $phone }}" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" autocomplete="tel-national" placeholder="98765 43210" autofocus
                                   oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                   @if($err->has('phone')) aria-invalid="true" aria-describedby="loginPhoneErr" @endif
                                   class="w-full h-12 pl-[5.5rem] pr-4 rounded-xl border {{ $err->has('phone') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15' }} bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono text-base font-bold tracking-wider placeholder:font-normal placeholder:font-sans placeholder:tracking-normal placeholder:text-slate-400 shadow-sm transition-all outline-none">
                        </div>
                        @if($err->has('phone'))
                            <p id="loginPhoneErr" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i>{{ $err->first('phone') }}
                            </p>
                        @endif
                    </div>

                    @if($demoCode)
                        <div class="p-3.5 rounded-2xl bg-amber-50/90 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/25 flex items-center justify-between gap-3 text-[12px] text-amber-900 dark:text-amber-200 shadow-sm transition-all hover:border-amber-300">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <i class="ph-fill ph-lightning"></i>
                                </span>
                                <div class="min-w-0">
                                    <span class="font-bold block text-[12px]">{{ __('auth_ui.demo_customer') }}</span>
                                    <span class="text-[11px] text-amber-700 dark:text-amber-300 font-mono">9988776655 · OTP: {{ $demoCode }}</span>
                                </div>
                            </div>
                            <button type="button" onclick="var i=document.getElementById('loginPhone');i.value='9988776655';i.focus();this.innerHTML='<i class=\'ph-bold ph-check\'></i> Applied!';setTimeout(()=>this.innerHTML='Auto Fill',2000);" 
                                    class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-sm transition-all active:scale-95 shrink-0 flex items-center gap-1">
                                Auto Fill
                            </button>
                        </div>
                    @endif

                    <button type="submit" class="w-full h-12 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:from-emerald-700 active:to-teal-700 text-white font-extrabold text-[14.5px] shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 transition-all duration-200 flex items-center justify-center gap-2 relative overflow-hidden group/btn">
                        <span class="absolute inset-0 w-1/2 h-full bg-white/20 skew-x-12 -translate-x-full group-hover/btn:translate-x-[300%] transition-transform duration-700 pointer-events-none"></span>
                        <span>{{ __('auth_ui.send_code') }}</span>
                        <i class="ph-bold ph-arrow-right text-base transform group-hover/btn:translate-x-1 transition-transform"></i>
                    </button>
                </form>

                <p class="mt-6 text-center text-[13px] text-slate-500 dark:text-slate-400">
                    {{ __('auth_ui.no_account') }}
                    <a href="{{ route('register') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline inline-flex items-center gap-0.5">
                        {{ __('auth_ui.create_account') }} <i class="ph-bold ph-caret-right text-xs"></i>
                    </a>
                </p>
            </div>

            <p class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 text-center text-[11.5px] text-slate-400 leading-relaxed">
                {!! __('auth_ui.continue_agree', [
                    'terms' => '<a href="' . e(route('pages.show', 'legal-information')) . '" class="underline font-semibold text-slate-600 dark:text-slate-300 hover:text-emerald-600">' . e(__('messages.terms')) . '</a>',
                    'privacy' => '<a href="' . e(route('pages.show', 'privacy-policy')) . '" class="underline font-semibold text-slate-600 dark:text-slate-300 hover:text-emerald-600">' . e(__('messages.privacy_policy')) . '</a>',
                ]) !!}
            </p>
        </div>
    </div>
</div>
@endsection
