@extends('frontend.layouts.app')

@section('title', __('auth_ui.tab_register') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $err = $errors->register;
    $lang = old('language', $pending['language'] ?? app()->getLocale());
    $registeredPhone = session('phone_registered');
    $termsLink = '<a href="' . e(route('pages.show', 'legal-information')) . '" target="_blank" class="font-bold text-brand-700 dark:text-brand-400 hover:underline">' . e(__('messages.terms')) . '</a>';
    $privacyLink = '<a href="' . e(route('pages.show', 'privacy-policy')) . '" target="_blank" class="font-bold text-brand-700 dark:text-brand-400 hover:underline">' . e(__('messages.privacy_policy')) . '</a>';
@endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-14">
    <div class="fx-card overflow-hidden grid md:grid-cols-2 shadow-[0_24px_60px_-30px_rgba(5,150,105,.35)]">
        @include('frontend.auth._panel', ['variant' => 'register'])

        <div class="p-6 sm:p-10">
            @include('frontend.auth._tabs', ['active' => 'register'])

            <div class="mt-7 flex items-center gap-3.5">
                <span class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-[1.65rem] shrink-0"><i class="ph-duotone ph-user-plus"></i></span>
                <div class="min-w-0">
                    <h1 class="text-[1.4rem] sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">{{ __('auth_ui.register_title') }}</h1>
                    <p class="mt-0.5 text-[13px] text-slate-500 dark:text-slate-400">{{ __('auth_ui.register_sub') }}</p>
                </div>
            </div>

            @if(session('auth_notice'))
                <div role="status" class="mt-5 flex items-start gap-2.5 p-3 rounded-xl bg-sky-50 dark:bg-sky-500/10 border border-sky-200 dark:border-sky-500/30 text-[12.5px] font-semibold text-sky-900 dark:text-sky-200">
                    <i class="ph-fill ph-info text-sky-500 text-base shrink-0"></i><span>{{ session('auth_notice') }}</span>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="mt-6 space-y-4" data-loading novalidate>
                @csrf

                <div>
                    <label for="regName" class="fx-label">{{ __('auth_ui.full_name') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                        <input type="text" id="regName" name="name" value="{{ old('name', $pending['name'] ?? '') }}" required minlength="2" maxlength="100" autocomplete="name" placeholder="{{ __('auth_ui.full_name_ph') }}" @if(!$phone || old('name') === null) autofocus @endif
                               class="fx-input !h-12 !pl-11 {{ $err->has('name') ? 'is-invalid' : '' }}" @if($err->has('name')) aria-invalid="true" @endif>
                    </div>
                    @if($err->has('name'))<p class="text-xs font-semibold text-rose-600 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $err->first('name') }}</p>@endif
                </div>

                <div>
                    <label for="regPhone" class="fx-label">{{ __('auth_ui.mobile') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-0 inset-y-0 pl-4 pr-3 flex items-center text-[14px] font-bold text-slate-500 border-r border-slate-200 dark:border-slate-700 my-2.5">+91</span>
                        <input type="tel" id="regPhone" name="phone" value="{{ $phone }}" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" autocomplete="tel-national" placeholder="98765 43210"
                               oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                               class="fx-input !h-12 !pl-[4.5rem] !text-base font-mono font-bold tracking-wider {{ $err->has('phone') ? 'is-invalid' : '' }}" @if($err->has('phone')) aria-invalid="true" @endif>
                    </div>
                    @if($err->has('phone'))
                        <p class="text-xs font-semibold text-rose-600 mt-1.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                            <i class="ph-fill ph-warning-circle"></i><span>{{ $err->first('phone') }}</span>
                            @if($registeredPhone)
                                <a href="{{ route('login', ['phone' => $registeredPhone]) }}" class="inline-flex items-center gap-1 font-bold text-brand-700 dark:text-brand-400 hover:underline">{{ __('auth_ui.log_in_instead') }}<i class="ph-bold ph-arrow-right"></i></a>
                            @endif
                        </p>
                    @endif
                </div>

                <div>
                    <label for="regEmail" class="fx-label">{{ __('auth_ui.email_optional') }} <span class="font-semibold text-slate-400">({{ __('auth_ui.optional') }})</span></label>
                    <div class="relative">
                        <i class="ph ph-envelope-simple absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                        <input type="email" id="regEmail" name="email" value="{{ old('email', $pending['email'] ?? '') }}" maxlength="150" autocomplete="email" placeholder="you@example.com"
                               class="fx-input !h-12 !pl-11 {{ $err->has('email') ? 'is-invalid' : '' }}" @if($err->has('email')) aria-invalid="true" @endif>
                    </div>
                    @if($err->has('email'))
                        <p class="text-xs font-semibold text-rose-600 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $err->first('email') }}</p>
                    @else
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('auth_ui.email_hint') }}</p>
                    @endif
                </div>

                <fieldset>
                    <legend class="fx-label">{{ __('auth_ui.preferred_language') }}</legend>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach(['en' => ['English', 'A'], 'gu' => ['ગુજરાતી', 'અ']] as $code => [$label, $glyph])
                            <label class="relative cursor-pointer">
                                <input type="radio" name="language" value="{{ $code }}" class="peer sr-only" @checked($lang === $code)>
                                <span class="flex items-center gap-2.5 h-12 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-[13.5px] font-bold text-slate-700 dark:text-slate-200 transition-all
                                             peer-checked:border-brand-500 peer-checked:bg-brand-50 dark:peer-checked:bg-brand-500/10 peer-checked:text-brand-800 dark:peer-checked:text-brand-200 peer-checked:ring-4 peer-checked:ring-brand-500/15 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-500/30">
                                    <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[13px] font-extrabold">{{ $glyph }}</span>
                                    {{ $label }}
                                </span>
                                <i class="ph-fill ph-check-circle absolute right-3 top-1/2 -translate-y-1/2 text-brand-600 dark:text-brand-400 text-lg opacity-0 scale-50 transition-all peer-checked:opacity-100 peer-checked:scale-100"></i>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="terms" value="1" @checked(old('terms')) required
                               class="mt-0.5 w-[18px] h-[18px] rounded-[5px] border-slate-300 dark:border-slate-600 text-brand-600 accent-emerald-600 shrink-0">
                        <span class="text-[12.5px] leading-5 text-slate-600 dark:text-slate-300">{!! __('auth_ui.accept_terms', ['terms' => $termsLink, 'privacy' => $privacyLink]) !!}</span>
                    </label>
                    @if($err->has('terms'))<p class="text-xs font-semibold text-rose-600 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $err->first('terms') }}</p>@endif
                </div>

                <button type="submit" class="fx-btn fx-btn-primary fx-btn-lg w-full !h-12 relative">
                    <span>{{ __('auth_ui.create_account') }}</span><i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>

            <p class="mt-5 text-center text-[13px] text-slate-500 dark:text-slate-400">
                {{ __('auth_ui.have_account') }}
                <a href="{{ route('login') }}" class="font-bold text-brand-700 dark:text-brand-400 hover:underline">{{ __('auth_ui.tab_login') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
