@extends('frontend.layouts.app')

@section('title', __('messages.offers') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $fmt = fn($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $tones = [
        ['from-amber-500 to-orange-500', 'text-amber-600 dark:text-amber-400'],
        ['from-emerald-500 to-teal-600', 'text-emerald-600 dark:text-emerald-400'],
        ['from-violet-500 to-fuchsia-500', 'text-violet-600 dark:text-violet-400'],
        ['from-sky-500 to-indigo-500', 'text-sky-600 dark:text-sky-400'],
    ];
@endphp
<div class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-teal-700 text-white">
    <i class="ph-duotone ph-seal-percent absolute -right-10 -top-10 text-[16rem] opacity-10"></i>
    <div class="relative max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-10 sm:py-14 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-[11px] font-bold uppercase tracking-wider"><i class="ph-fill ph-ticket"></i>{{ $gu ? 'ખાસ બચત અને કૂપન' : 'Exclusive savings & coupons' }}</span>
        <h1 class="mt-3 text-3xl sm:text-5xl font-extrabold tracking-tight">{{ __('messages.offers') }}</h1>
        <p class="mt-2 text-[14px] sm:text-base text-brand-100 max-w-xl mx-auto">{{ $gu ? 'ચેકઆઉટ પર આ કૂપન કોડ વાપરો અને તાજી કરિયાણા પર તરત બચત મેળવો.' : 'Use these codes at checkout for instant discounts on fresh groceries and everyday essentials.' }}</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @forelse($offers as $offer)
            @php [$grad, $txt] = $tones[$loop->index % count($tones)]; @endphp
            <article class="fx-card overflow-hidden flex flex-col hover:shadow-xl hover:shadow-slate-900/5 transition-shadow">
                <div class="p-5 flex items-start gap-4">
                    <span class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $grad }} text-white flex items-center justify-center text-2xl shrink-0 shadow-lg"><i class="ph-fill ph-gift"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-2xl font-extrabold {{ $txt }} leading-none">
                            {{ $offer->discount_type === 'percentage' ? $fmt($offer->discount_value) . '% ' . __('messages.off') : '₹' . $fmt($offer->discount_value) . ' ' . ($gu ? 'ફ્લેટ' : 'FLAT OFF') }}
                        </p>
                        <h3 class="mt-1.5 text-[15px] font-bold text-slate-900 dark:text-white leading-snug">{{ $offer->localized_title }}</h3>
                        <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-3">{{ $offer->localized_description ?: ($gu ? 'ચેકઆઉટ પર આ કોડ લાગુ કરો.' : 'Apply this code at checkout to save on your order.') }}</p>
                    </div>
                </div>
                <dl class="px-5 pb-4 grid grid-cols-2 gap-3 text-[12px] mt-auto">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                        <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400">{{ $gu ? 'ન્યૂનતમ ઓર્ડર' : 'Min. order' }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">₹{{ number_format($offer->min_order_amount, 0) }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                        <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400">{{ $offer->valid_to ? ($gu ? 'માન્ય સુધી' : 'Valid until') : ($offer->max_discount_amount ? ($gu ? 'મહત્તમ છૂટ' : 'Max discount') : ($gu ? 'માન્યતા' : 'Validity')) }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">
                            @if($offer->valid_to){{ $offer->valid_to->format('d M Y') }}@elseif($offer->max_discount_amount)₹{{ number_format($offer->max_discount_amount, 0) }}@else{{ $gu ? 'મર્યાદિત સમય' : 'Limited time' }}@endif
                        </dd>
                    </div>
                </dl>
                <div class="relative border-t-2 border-dashed border-slate-200 dark:border-slate-700 px-5 py-3.5 flex items-center justify-between gap-3 bg-slate-50/60 dark:bg-slate-900">
                    <span class="absolute -left-3 -top-3 w-6 h-6 rounded-full" style="background: var(--fx-bg)"></span>
                    <span class="absolute -right-3 -top-3 w-6 h-6 rounded-full" style="background: var(--fx-bg)"></span>
                    <span class="font-mono text-base font-extrabold tracking-widest text-slate-900 dark:text-white">{{ $offer->code }}</span>
                    <button type="button" data-copy="{{ $offer->code }}" class="fx-btn fx-btn-dark fx-btn-sm"><i class="ph-bold ph-copy"></i><span data-copy-label>{{ $gu ? 'કૉપિ કરો' : 'Copy code' }}</span></button>
                </div>
            </article>
        @empty
            <div class="col-span-full fx-card py-14 text-center">
                <span class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-3xl"><i class="ph-duotone ph-ticket"></i></span>
                <h3 class="font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'હાલમાં કોઈ ઑફર નથી' : 'No active offers right now' }}</h3>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'નવી ઑફર માટે ફરી તપાસો!' : 'Please check back soon for new discount coupons!' }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8 fx-card p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <span class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-2xl shrink-0"><i class="ph-duotone ph-shopping-cart-simple"></i></span>
        <div class="flex-1">
            <p class="font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'કૂપન કેવી રીતે વાપરવું?' : 'How to use a coupon' }}</p>
            <p class="text-[13px] text-slate-500 dark:text-slate-400">{{ $gu ? 'કોડ કૉપિ કરો, કાર્ટમાં વસ્તુઓ ઉમેરો અને ચેકઆઉટ પર "લાગુ કરો" દબાવો.' : 'Copy a code, add items to your cart and paste it in the coupon box at checkout.' }}</p>
        </div>
        <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary shrink-0">{{ __('messages.start_shopping') }}<i class="ph-bold ph-arrow-right"></i></a>
    </div>
</div>
@endsection
