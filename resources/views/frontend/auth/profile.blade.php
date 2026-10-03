@extends('frontend.layouts.app')

@section('title', (app()->getLocale() === 'gu' ? 'મારી પ્રોફાઇલ' : 'My profile') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $user = auth()->user();
    $ordersCount = $user->orders()->count();
    $addrCount = $user->addresses()->count();
    $wishCountP = $user->wishlists()->count();
    $lang = old('language', $user->language ?: app()->getLocale());
@endphp
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">
    <div class="mb-5 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'મારી પ્રોફાઇલ' : 'My profile' }}</h1>
        <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'તમારી વ્યક્તિગત વિગતો અને ભાષા પસંદગી મેનેજ કરો' : 'Manage your personal details and language preference' }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 items-start">
        <aside class="order-2 md:order-1 space-y-4 md:sticky md:top-36">
            <div class="fx-card p-5 text-center">
                <span class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex items-center justify-center text-3xl font-extrabold mx-auto shadow-lg shadow-brand-600/25">
                    {{ strtoupper(mb_substr($user->name ?: 'U', 0, 1)) }}
                </span>
                <h3 class="mt-3 font-extrabold text-lg text-slate-900 dark:text-white">{{ $user->name ?: ($gu ? 'ગ્રાહક' : 'Customer') }}</h3>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 font-mono">+91 {{ $user->phone }}</p>
                @if($user->email)<p class="text-[12px] text-slate-400 truncate">{{ $user->email }}</p>@endif
                <span class="mt-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300 text-[11px] font-bold"><i class="ph-fill ph-seal-check"></i>{{ $gu ? 'ચકાસાયેલ ગ્રાહક' : 'Verified customer' }}</span>
                <div class="mt-5 grid grid-cols-3 gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('orders.index') }}" class="rounded-xl py-2 hover:bg-slate-50 dark:hover:bg-slate-800"><p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $ordersCount }}</p><p class="text-[11px] text-slate-500">{{ __('messages.orders') }}</p></a>
                    <a href="{{ route('addresses.index') }}" class="rounded-xl py-2 hover:bg-slate-50 dark:hover:bg-slate-800"><p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $addrCount }}</p><p class="text-[11px] text-slate-500">{{ $gu ? 'સરનામાં' : 'Addresses' }}</p></a>
                    <a href="{{ route('wishlist.index') }}" class="rounded-xl py-2 hover:bg-slate-50 dark:hover:bg-slate-800"><p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $wishCountP }}</p><p class="text-[11px] text-slate-500">{{ __('messages.wishlist') }}</p></a>
                </div>
            </div>

            <nav class="fx-card p-2">
                @foreach([
                    [route('orders.index'), 'ph-package', 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400', __('messages.order_history')],
                    [route('wishlist.index'), 'ph-heart', 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400', __('messages.wishlist')],
                    [route('addresses.index'), 'ph-map-pin', 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', __('messages.saved_addresses')],
                    [route('order.track'), 'ph-crosshair', 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400', $gu ? 'ઓર્ડર ટ્રેક કરો' : 'Track an order'],
                ] as [$href, $ic, $tone, $label])
                    <a href="{{ $href }}" class="flex items-center gap-3 p-2.5 rounded-xl text-[13px] font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <span class="w-9 h-9 rounded-lg {{ $tone }} flex items-center justify-center"><i class="ph-fill {{ $ic }}"></i></span>
                        <span class="flex-1">{{ $label }}</span>
                        <i class="ph-bold ph-caret-right text-xs text-slate-400"></i>
                    </a>
                @endforeach
                <form action="{{ route('customer.logout') }}" method="POST" class="mt-1 pt-1 border-t border-slate-100 dark:border-slate-800">
                    @csrf
                    <button class="w-full flex items-center gap-3 p-2.5 rounded-xl text-[13px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                        <span class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center"><i class="ph-bold ph-sign-out"></i></span>{{ __('messages.logout') }}
                    </button>
                </form>
            </nav>
        </aside>

        <div class="order-1 md:order-2 md:col-span-2">
            <form action="{{ route('profile.update') }}" method="POST" class="fx-card p-4 sm:p-7 space-y-5" data-loading>
                @csrf
                @method('PUT')
                <h3 class="font-extrabold text-slate-900 dark:text-white text-[15px] flex items-center gap-2"><i class="ph-duotone ph-user-circle text-brand-600 text-xl"></i>{{ $gu ? 'વ્યક્તિગત માહિતી' : 'Personal information' }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="pfName" class="fx-label">{{ $gu ? 'પૂરું નામ' : 'Full name' }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="pfName" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="fx-input @error('name') is-invalid @enderror">
                        @error('name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="pfPhone" class="fx-label">{{ $gu ? 'મોબાઇલ નંબર' : 'Mobile number' }}</label>
                        <input type="text" id="pfPhone" value="+91 {{ $user->phone }}" disabled class="fx-input font-mono">
                        <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1"><i class="ph-fill ph-lock-simple"></i>{{ $gu ? 'ચકાસાયેલ નંબર બદલી શકાતો નથી' : 'Verified number — cannot be changed' }}</p>
                    </div>
                    <div>
                        <label for="pfEmail" class="fx-label">{{ $gu ? 'ઇમેઇલ' : 'Email' }}</label>
                        <input type="email" id="pfEmail" name="email" value="{{ old('email', $user->email) }}" placeholder="example@mail.com" autocomplete="email" class="fx-input @error('email') is-invalid @enderror">
                        @error('email')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <fieldset>
                    <legend class="fx-label">{{ $gu ? 'પસંદગીની ભાષા' : 'Preferred language' }}</legend>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['en' => ['English', 'EN'], 'gu' => ['ગુજરાતી', 'ગુ']] as $code => [$name, $abbr])
                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 dark:has-[:checked]:bg-brand-500/10 cursor-pointer transition-colors">
                                <input type="radio" name="language" value="{{ $code }}" {{ $lang === $code ? 'checked' : '' }} class="w-4 h-4 accent-emerald-600">
                                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-[12px] font-extrabold text-slate-600 dark:text-slate-300 flex items-center justify-center">{{ $abbr }}</span>
                                <span class="text-[14px] font-bold text-slate-900 dark:text-white">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('language')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </fieldset>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="submit" class="fx-btn fx-btn-primary w-full sm:w-auto"><i class="ph-bold ph-floppy-disk"></i><span>{{ $gu ? 'ફેરફારો સાચવો' : 'Save changes' }}</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
