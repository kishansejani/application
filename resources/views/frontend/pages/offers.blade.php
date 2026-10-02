@extends('frontend.layouts.app')

@section('title', __('messages.offers') . ' & ' . __('messages.coupons') . ' - ' . config('app.name'))

@section('content')
<div class="bg-gradient-to-r from-amber-500 via-primary-600 to-emerald-600 py-12 px-4 sm:px-6 lg:px-8 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="max-w-7xl mx-auto relative z-10 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-bold uppercase tracking-wider mb-3">
            <i class="fas fa-tags"></i>
            {{ __('messages.exclusive_discounts') ?? 'Exclusive Savings & Coupons' }}
        </span>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight">{{ __('messages.special_offers') }}</h1>
        <p class="text-emerald-100 mt-2 max-w-xl mx-auto text-sm sm:text-base">
            {{ __('messages.offers_subtitle') ?? 'Use these promo coupons at checkout to get instant discounts on fresh groceries and everyday essentials.' }}
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($offers as $offer)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border-2 border-dashed border-primary-200 dark:border-slate-700 overflow-hidden shadow-sm hover:shadow-xl transition relative group">
            <!-- Top badge -->
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-100 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 text-xs font-bold">
                            @if($offer->type === 'percentage')
                                {{ $offer->discount_value }}% OFF
                            @else
                                ₹{{ $offer->discount_value }} FLAT OFF
                            @endif
                        </span>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-2">{{ $offer->title }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-gift"></i>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                    {{ $offer->description ?? __('messages.apply_coupon_desc') ?? 'Apply this code at checkout to enjoy savings on your order.' }}
                </p>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        <span class="block text-[10px] uppercase font-semibold text-slate-400">{{ __('messages.min_order') ?? 'Min. Order' }}</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">₹{{ number_format($offer->min_order_amount, 2) }}</span>
                    </div>
                    @if($offer->expires_at)
                    <div class="text-right">
                        <span class="block text-[10px] uppercase font-semibold text-slate-400">{{ __('messages.valid_until') ?? 'Valid Until' }}</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $offer->expires_at->format('d M, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Coupon Code Strip -->
            <div class="bg-slate-50 dark:bg-slate-900/80 px-6 py-3 border-t border-dashed border-primary-200 dark:border-slate-700 flex items-center justify-between">
                <div class="font-mono text-sm font-black text-primary-600 dark:text-primary-400 tracking-wider">
                    {{ $offer->code }}
                </div>
                <button type="button" onclick="copyCoupon('{{ $offer->code }}')"
                        class="px-3 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-primary-500 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 transition active:scale-95 flex items-center gap-1">
                    <i class="far fa-copy text-primary-500"></i>
                    <span>{{ __('messages.copy_code') ?? 'Copy' }}</span>
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fas fa-tags"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ __('messages.no_offers_now') ?? 'No active offers available' }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ __('messages.check_back_later') ?? 'Please check back soon for exciting discount coupons!' }}</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function copyCoupon(code) {
    navigator.clipboard.writeText(code).then(() => {
        toastr.success(`Coupon code "${code}" copied to clipboard!`);
    }).catch(() => {
        toastr.info(`Coupon code: ${code}`);
    });
}
</script>
@endsection
