@extends('frontend.layouts.app')

@section('title', __('messages.login_with_phone') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-10 space-y-6">

        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fa-solid fa-mobile-screen"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.login_with_phone') }}</h1>
            <p class="text-xs text-slate-500">We will send a 4-digit verification code to your mobile number.</p>
        </div>

        <form action="{{ route('customer.otp.send') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    {{ __('messages.enter_phone') }}
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-500">
                        +91
                    </span>
                    <input type="tel" name="phone" value="{{ old('phone', '9988776655') }}" required pattern="[0-9]{10}" placeholder="9876543210" autofocus
                        class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                    <i class="fa-solid fa-shield-check text-emerald-500"></i>
                    <span>Demo Customer: 9988776655 (OTP: 1234)</span>
                </p>
            </div>

            <button type="submit" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center gap-2">
                <span>{{ __('messages.send_otp') }}</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </button>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center text-[11px] text-slate-400">
            By continuing, you agree to our <a href="{{ route('pages.show', 'legal-information') }}" class="underline text-slate-600 font-semibold">{{ __('messages.terms') }}</a> and <a href="{{ route('pages.show', 'privacy-policy') }}" class="underline text-slate-600 font-semibold">{{ __('messages.privacy_policy') }}</a>.
        </div>
    </div>
</div>
@endsection
