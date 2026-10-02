@extends('frontend.layouts.app')

@section('title', __('messages.verify_otp') . ' - ' . config('app.name'))

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-100 dark:border-slate-700 p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 mb-4 shadow-inner">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('messages.verify_otp') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                {{ __('messages.otp_sent') }}: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $phone }}</span>
            </p>
            <div class="mt-2 inline-block px-3 py-1 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-full text-xs font-semibold text-amber-700 dark:text-amber-400">
                <i class="fas fa-info-circle mr-1"></i> Demo OTP: <strong class="text-base tracking-widest text-primary-600 dark:text-primary-400">1234</strong>
            </div>
        </div>

        <form action="{{ route('auth.verify') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="phone" value="{{ $phone }}">

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3 text-center">
                    {{ __('messages.enter_otp') }}
                </label>
                <div class="flex justify-center gap-3">
                    <input type="text" name="otp" id="otp-input" maxlength="4" required autofocus
                           placeholder="1234"
                           class="w-48 text-center tracking-[1em] text-3xl font-black py-3 border-2 border-slate-300 dark:border-slate-600 rounded-2xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 shadow-inner">
                </div>
                @error('otp')
                <p class="text-xs text-rose-500 text-center mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-2xl shadow-lg shadow-primary-600/30 transition transform active:scale-95">
                <i class="fas fa-check-circle"></i>
                <span>{{ __('messages.verify_otp') }}</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <form action="{{ route('auth.send-otp') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="phone" value="{{ $phone }}">
                <button type="submit" class="text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-redo-alt text-xs"></i>
                    <span>{{ __('messages.resend_otp') }}</span>
                </button>
            </form>
            <div class="mt-3">
                <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400">
                    <i class="fas fa-arrow-left mr-1"></i> {{ __('messages.change_phone') ?? 'Change Phone Number' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
