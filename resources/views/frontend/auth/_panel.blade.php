{{-- Brand side of the login / sign-up card. @include('frontend.auth._panel', ['variant' => 'login'|'register']) --}}
@php
    $reg = ($variant ?? 'login') === 'register';
    $perks = $reg
        ? [
            ['ph-gift', __('auth_ui.perk_welcome'), 'bg-amber-400/20 text-amber-300 ring-amber-400/30'],
            ['ph-lightning', __('auth_ui.perk_delivery'), 'bg-emerald-400/20 text-emerald-300 ring-emerald-400/30'],
            ['ph-shield-check', __('auth_ui.perk_otp'), 'bg-sky-400/20 text-sky-300 ring-sky-400/30']
          ]
        : [
            ['ph-lightning', __('auth_ui.perk_delivery'), 'bg-amber-400/20 text-amber-300 ring-amber-400/30'],
            ['ph-seal-percent', __('auth_ui.perk_offers'), 'bg-rose-400/20 text-rose-300 ring-rose-400/30'],
            ['ph-shield-check', __('auth_ui.perk_otp'), 'bg-emerald-400/20 text-emerald-300 ring-emerald-400/30']
          ];
@endphp
<div class="hidden md:flex flex-col justify-between p-8 lg:p-10 bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900 text-white relative overflow-hidden select-none">
    {{-- Animated Ambient Background Orbs --}}
    <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-emerald-400/20 blur-3xl animate-pulse pointer-events-none" style="animation-duration: 6s;"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-teal-300/20 blur-3xl animate-pulse pointer-events-none" style="animation-duration: 8s;"></div>
    <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(#fff 1.5px, transparent 1.5px); background-size: 24px 24px;" aria-hidden="true"></div>

    {{-- Watermark Decorative Icon --}}
    <i class="ph-duotone {{ $reg ? 'ph-shopping-bag-open' : 'ph-basket' }} absolute -right-8 -bottom-10 text-[16rem] opacity-[0.06] transform -rotate-12 pointer-events-none" aria-hidden="true"></i>

    {{-- Top Header / Trust Badge --}}
    <div class="relative z-10">
        <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 shadow-lg text-[12px] font-bold text-emerald-100 mb-6">
            <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-300"></span>
            </span>
            <span>{{ __('messages.tagline') }}</span>
        </div>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-lg border border-white/25 flex items-center justify-center text-2xl shadow-inner text-emerald-200">
                <i class="ph-fill ph-basket"></i>
            </div>
            <div>
                <span class="text-xs uppercase tracking-widest font-extrabold text-emerald-300/90 block">{{ __('messages.store_name') }}</span>
                <span class="text-[11px] text-white/70 font-medium">Ahmedabad's Fresh Grocery Express</span>
            </div>
        </div>

        <h2 class="text-2xl lg:text-3xl font-extrabold leading-tight text-white tracking-tight drop-shadow-sm">
            {{ $reg ? __('auth_ui.panel_register_title') : __('auth_ui.panel_title') }}
        </h2>
        <p class="mt-3 text-[13.5px] text-emerald-100/90 leading-relaxed font-medium">
            {{ $reg ? __('auth_ui.panel_register_sub') : __('auth_ui.panel_sub') }}
        </p>
    </div>

    {{-- Feature Perks with Glassmorphism --}}
    <div class="relative z-10 my-8 space-y-3">
        @foreach($perks as [$ic, $txt, $ringClasses])
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-white/[0.08] backdrop-blur-md border border-white/15 hover:bg-white/[0.14] hover:border-white/30 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shrink-0 ring-1 {{ $ringClasses }}">
                    <i class="ph-fill {{ $ic }}"></i>
                </span>
                <span class="text-[13px] font-semibold text-white/95 leading-snug">{{ $txt }}</span>
            </div>
        @endforeach
    </div>

    {{-- Bottom Trust Guarantee --}}
    <div class="relative z-10 pt-4 border-t border-white/15 flex items-center justify-between text-[12px] text-emerald-100/80">
        <div class="flex items-center gap-1.5">
            <i class="ph-fill ph-shield-check text-emerald-300 text-base"></i>
            <span class="font-semibold">100% Safe & Secure</span>
        </div>
        <div class="flex items-center gap-1">
            <span class="text-amber-300">★ ★ ★ ★ ★</span>
            <span class="font-bold text-white text-[11px] ml-1">4.9/5</span>
        </div>
    </div>
</div>
