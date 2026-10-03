{{-- Brand side of the login / sign-up card. @include('frontend.auth._panel', ['variant' => 'login'|'register']) --}}
@php
    $reg = ($variant ?? 'login') === 'register';
    $perks = $reg
        ? [['ph-gift', __('auth_ui.perk_welcome')], ['ph-lightning', __('auth_ui.perk_delivery')], ['ph-shield-check', __('auth_ui.perk_otp')]]
        : [['ph-lightning', __('auth_ui.perk_delivery')], ['ph-seal-percent', __('auth_ui.perk_offers')], ['ph-shield-check', __('auth_ui.perk_otp')]];
@endphp
<div class="hidden md:flex flex-col justify-between p-8 lg:p-10 bg-gradient-to-br from-brand-600 via-brand-700 to-teal-800 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-[.08]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;" aria-hidden="true"></div>
    <i class="ph-duotone {{ $reg ? 'ph-shopping-bag-open' : 'ph-basket' }} absolute -right-10 -bottom-10 text-[14rem] opacity-10" aria-hidden="true"></i>
    <div class="relative">
        <span class="w-11 h-11 rounded-xl bg-white/15 ring-1 ring-white/20 flex items-center justify-center text-2xl"><i class="ph-fill ph-basket"></i></span>
        <h2 class="mt-6 text-2xl lg:text-3xl font-extrabold leading-tight">{{ $reg ? __('auth_ui.panel_register_title') : __('auth_ui.panel_title') }}</h2>
        <p class="mt-3 text-[14px] text-brand-100">{{ $reg ? __('auth_ui.panel_register_sub') : __('auth_ui.panel_sub') }}</p>
    </div>
    <ul class="relative space-y-3 text-[13px] font-semibold mt-8">
        @foreach($perks as [$ic, $txt])
            <li class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="ph-fill {{ $ic }}"></i></span>{{ $txt }}</li>
        @endforeach
    </ul>
</div>
