@extends('frontend.layouts.app')

@section('title', __('auth_ui.tab_register') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $err = $errors->register;
    $lang = old('language', $pending['language'] ?? app()->getLocale());
    $registeredPhone = session('phone_registered');
    $termsLink = '<a href="' . e(route('pages.show', 'legal-information')) . '" target="_blank" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">' . e(__('messages.terms')) . '</a>';
    $privacyLink = '<a href="' . e(route('pages.show', 'privacy-policy')) . '" target="_blank" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">' . e(__('messages.privacy_policy')) . '</a>';
@endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 relative">
    {{-- Outer Ambient Glow --}}
    <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-3/4 h-80 bg-gradient-to-tr from-emerald-500/15 via-teal-500/10 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <div class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-[0_24px_60px_-15px_rgba(5,150,105,0.18)] border border-slate-200/80 dark:border-slate-800 overflow-hidden grid md:grid-cols-2">
        {{-- Left Brand Showcase Panel --}}
        @include('frontend.auth._panel', ['variant' => 'register'])

        {{-- Right Form Section --}}
        <div class="p-6 sm:p-10 flex flex-col justify-between">
            <div>
                @include('frontend.auth._tabs', ['active' => 'register'])

                <div class="mt-8 flex items-center gap-4">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-emerald-500/15 to-teal-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-2xl shrink-0 shadow-sm">
                        <i class="ph-duotone ph-user-plus animate-pulse" style="animation-duration: 3s;"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 mb-1">
                            <i class="ph-fill ph-sparkle text-xs text-amber-500"></i> Quick Sign Up
                        </span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                            {{ __('auth_ui.register_title') }}
                        </h1>
                        <p class="mt-0.5 text-[13px] text-slate-500 dark:text-slate-400">
                            {{ __('auth_ui.register_sub') }}
                        </p>
                    </div>
                </div>

                @if(session('auth_notice'))
                    <div role="status" class="mt-5 flex items-start gap-2.5 p-3.5 rounded-2xl bg-sky-50 dark:bg-sky-500/10 border border-sky-200 dark:border-sky-500/30 text-[12.5px] font-semibold text-sky-900 dark:text-sky-200 shadow-sm animate-fade-in">
                        <i class="ph-fill ph-info text-sky-500 text-lg shrink-0 mt-0.5"></i>
                        <span>{{ session('auth_notice') }}</span>
                    </div>
                @endif

                <form action="{{ route('register.post') }}" method="POST" class="mt-6 space-y-4" data-loading novalidate>
                    @csrf

                    <div>
                        <label for="regName" class="text-[13px] font-bold text-slate-700 dark:text-slate-200 block mb-1.5">
                            {{ __('auth_ui.full_name') }} <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ph ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                            <input type="text" id="regName" name="name" value="{{ old('name', $pending['name'] ?? '') }}" required minlength="2" maxlength="100" autocomplete="name" placeholder="{{ __('auth_ui.full_name_ph') }}" @if(!$phone || old('name') === null) autofocus @endif
                                   class="w-full h-12 pl-10 pr-4 rounded-xl border {{ $err->has('name') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15' }} bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm font-semibold placeholder:text-slate-400 placeholder:font-normal shadow-sm transition-all outline-none" @if($err->has('name')) aria-invalid="true" @endif>
                        </div>
                        @if($err->has('name'))
                            <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i>{{ $err->first('name') }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="regPhone" class="text-[13px] font-bold text-slate-700 dark:text-slate-200">
                                {{ __('auth_ui.mobile') }} <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] font-medium text-slate-400">10 digits Indian mobile</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-0 inset-y-0 pl-3.5 pr-3 flex items-center gap-1.5 text-[13.5px] font-extrabold text-slate-600 dark:text-slate-300 border-r border-slate-200 dark:border-slate-700 my-2 select-none bg-slate-50/50 dark:bg-slate-800/50 rounded-l-xl">
                                <span class="text-base" role="img" aria-label="India flag">🇮🇳</span> +91
                            </span>
                            <input type="tel" id="regPhone" name="phone" value="{{ $phone }}" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" autocomplete="tel-national" placeholder="98765 43210"
                                   oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                   class="w-full h-12 pl-[5.5rem] pr-4 rounded-xl border {{ $err->has('phone') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15' }} bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono text-base font-bold tracking-wider placeholder:font-normal placeholder:font-sans placeholder:tracking-normal placeholder:text-slate-400 shadow-sm transition-all outline-none" @if($err->has('phone')) aria-invalid="true" @endif>
                        </div>
                        @if($err->has('phone'))
                            <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                <i class="ph-fill ph-warning-circle"></i><span>{{ $err->first('phone') }}</span>
                                @if($registeredPhone)
                                    <a href="{{ route('login', ['phone' => $registeredPhone]) }}" class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        {{ __('auth_ui.log_in_instead') }} <i class="ph-bold ph-arrow-right"></i>
                                    </a>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="regEmail" class="text-[13px] font-bold text-slate-700 dark:text-slate-200">
                                {{ __('auth_ui.email_optional') }} <span class="font-normal text-slate-400 text-xs">({{ __('auth_ui.optional') }})</span>
                            </label>
                            <span class="text-[11px] text-slate-400">{{ __('auth_ui.email_hint') }}</span>
                        </div>
                        <div class="relative">
                            <i class="ph ph-envelope-simple absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                            <input type="email" id="regEmail" name="email" value="{{ old('email', $pending['email'] ?? '') }}" maxlength="150" autocomplete="email" placeholder="you@example.com"
                                   class="w-full h-12 pl-10 pr-4 rounded-xl border {{ $err->has('email') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15' }} bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm font-semibold placeholder:text-slate-400 placeholder:font-normal shadow-sm transition-all outline-none" @if($err->has('email')) aria-invalid="true" @endif>
                        </div>
                        @if($err->has('email'))
                            <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i>{{ $err->first('email') }}
                            </p>
                        @endif
                    </div>

                    <fieldset>
                        <legend class="text-[13px] font-bold text-slate-700 dark:text-slate-200 mb-2">{{ __('auth_ui.preferred_language') }}</legend>
                        <div class="grid grid-cols-2 gap-2.5">
                            @foreach(['en' => ['English', 'A'], 'gu' => ['ગુજરાતી', 'અ']] as $code => [$label, $glyph])
                                <label class="relative cursor-pointer group/lang">
                                    <input type="radio" name="language" value="{{ $code }}" class="peer sr-only" @checked($lang === $code)>
                                    <span class="flex items-center gap-3 h-12 px-3.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-[13.5px] font-bold text-slate-700 dark:text-slate-200 transition-all duration-200
                                                 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/60 dark:peer-checked:bg-emerald-500/10 peer-checked:text-emerald-800 dark:peer-checked:text-emerald-200 peer-checked:ring-4 peer-checked:ring-emerald-500/15 group-hover/lang:border-slate-300">
                                        <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 peer-checked:bg-emerald-500/20 flex items-center justify-center text-[13px] font-black text-slate-700 dark:text-slate-300 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300">{{ $glyph }}</span>
                                        <span>{{ $label }}</span>
                                    </span>
                                    <i class="ph-fill ph-check-circle absolute right-3 top-1/2 -translate-y-1/2 text-emerald-600 dark:text-emerald-400 text-lg opacity-0 scale-50 transition-all duration-200 peer-checked:opacity-100 peer-checked:scale-100"></i>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="pt-1">
                        <label class="flex items-start gap-3 cursor-pointer select-none group/terms">
                            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required
                                   class="mt-1 w-[18px] h-[18px] rounded-[5px] border-slate-300 dark:border-slate-600 text-emerald-600 accent-emerald-600 shrink-0 cursor-pointer">
                            <span class="text-[12.5px] leading-5 text-slate-600 dark:text-slate-300">{!! __('auth_ui.accept_terms', ['terms' => $termsLink, 'privacy' => $privacyLink]) !!}</span>
                        </label>
                        @if($err->has('terms'))
                            <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i>{{ $err->first('terms') }}
                            </p>
                        @endif
                    </div>

                    <button type="submit" class="w-full h-12 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:from-emerald-700 active:to-teal-700 text-white font-extrabold text-[14.5px] shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 transition-all duration-200 flex items-center justify-center gap-2 relative overflow-hidden group/btn mt-2">
                        <span class="absolute inset-0 w-1/2 h-full bg-white/20 skew-x-12 -translate-x-full group-hover/btn:translate-x-[300%] transition-transform duration-700 pointer-events-none"></span>
                        <span>{{ __('auth_ui.create_account') }}</span>
                        <i class="ph-bold ph-arrow-right text-base transform group-hover/btn:translate-x-1 transition-transform"></i>
                    </button>
                </form>

                <p class="mt-6 text-center text-[13px] text-slate-500 dark:text-slate-400">
                    {{ __('auth_ui.have_account') }}
                    <a href="{{ route('login') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline inline-flex items-center gap-0.5">
                        {{ __('auth_ui.tab_login') }} <i class="ph-bold ph-caret-right text-xs"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
