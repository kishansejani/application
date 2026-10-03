@extends('frontend.layouts.app')

@section('title', __('messages.shopping_cart') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $threshold = 499;
    $diff = max(0, $threshold - $subtotal);
    $percent = min(100, round(($subtotal / $threshold) * 100));
    $deliveryFee = $subtotal >= $threshold ? 0 : 40;
    $savings = $cartItems->sum(fn($i) => $i->product->has_discount ? ($i->product->price - $i->product->effective_price) * $i->quantity : 0);
@endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <div class="flex items-end justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.shopping_cart') }}</h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">
                {{ $cartItems->isEmpty() ? ($gu ? 'તમારી બેગ ખાલી છે' : 'Your bag is empty') : $cartItems->sum('quantity') . ' ' . ($gu ? 'વસ્તુઓ તમારી બેગમાં' : 'items in your bag') }}
            </p>
        </div>
        <a href="{{ route('products.index') }}" class="fx-btn fx-btn-ghost !px-2 sm:!px-3 shrink-0"><i class="ph-bold ph-plus"></i><span>{{ $gu ? 'વધુ ઉમેરો' : 'Add more items' }}</span></a>
    </div>

    @if($cartItems->isEmpty())
        <div class="fx-card max-w-lg mx-auto py-14 px-6 text-center">
            <span class="w-20 h-20 rounded-full bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-4xl mx-auto mb-4"><i class="ph-duotone ph-shopping-bag"></i></span>
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ __('messages.your_cart_is_empty') }}</h3>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'તાજાં ફળો, શાકભાજી, નાસ્તો અને રોજિંદી જરૂરિયાતો શોધો.' : 'Explore fresh fruits, vegetables, snacks and grocery essentials.' }}</p>
            <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary fx-btn-lg mt-6">{{ __('messages.start_shopping') }}<i class="ph-bold ph-arrow-right"></i></a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-8 items-start">
            <div class="lg:col-span-2 space-y-4">
                <!-- Free delivery meter -->
                <div class="fx-card p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3 text-[13px] font-bold">
                        <span class="flex items-center gap-2 text-slate-800 dark:text-slate-100">
                            <i class="ph-fill ph-truck text-brand-600 text-lg"></i>
                            @if($diff > 0)
                                <span>{{ $gu ? 'મફત ડિલિવરી માટે' : 'Add' }} <span class="text-brand-700 dark:text-brand-400">₹{{ number_format($diff, 2) }}</span> {{ $gu ? 'વધુ ઉમેરો' : 'more for FREE delivery' }}</span>
                            @else
                                <span class="text-emerald-700 dark:text-emerald-400">{{ $gu ? 'તમને મફત ડિલિવરી મળી ગઈ!' : 'You unlocked FREE delivery!' }}</span>
                            @endif
                        </span>
                        <span class="text-slate-400">{{ $percent }}%</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-teal-400" style="width: {{ $percent }}%"></div>
                    </div>
                    <p class="mt-3 text-[12px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <i class="ph-fill ph-lightning text-amber-500"></i>
                        <span>{{ $gu ? 'ડિલિવરી સ્લોટ' : 'Delivery slot' }}: <strong class="text-slate-700 dark:text-slate-200">{{ $gu ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</strong></span>
                    </p>
                </div>

                <!-- Items -->
                <div class="fx-card overflow-hidden">
                    <div class="px-4 sm:px-6 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $gu ? 'કાર્ટની વસ્તુઓ' : 'Cart items' }} ({{ $cartItems->sum('quantity') }})</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($cartItems as $item)
                            <li class="p-4 sm:px-6 flex gap-3 sm:gap-4" data-cart-row="{{ $item->id }}">
                                <a href="{{ route('products.show', $item->product->slug) }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0">
                                    <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->localized_name }}" class="w-full h-full object-cover" loading="lazy">
                                </a>
                                <div class="flex-1 min-w-0 flex flex-col">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $item->product->slug) }}" class="font-bold text-[14px] text-slate-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400 line-clamp-2">{{ $item->product->localized_name }}</a>
                                            <p class="text-[12px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $item->product->unit }} · ₹{{ number_format($item->product->effective_price, 2) }}
                                                @if($item->product->has_discount)<span class="line-through text-slate-400 ml-1">₹{{ number_format($item->product->price, 2) }}</span>@endif
                                            </p>
                                        </div>
                                        <button type="button" onclick="cartRowRemove({{ $item->id }}, this)" class="relative w-8 h-8 shrink-0 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center" title="{{ $gu ? 'દૂર કરો' : 'Remove' }}" aria-label="{{ $gu ? 'દૂર કરો' : 'Remove' }}">
                                            <i class="ph ph-trash text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="mt-auto pt-2 flex items-center justify-between gap-3">
                                        <div class="fx-stepper w-28 !h-9" role="group" aria-label="{{ $gu ? 'જથ્થો' : 'Quantity' }}">
                                            <button type="button" onclick="cartRowUpdate({{ $item->id }}, {{ $item->quantity - 1 }}, this)" aria-label="{{ $gu ? 'ઘટાડો' : 'Decrease' }}"><i class="ph-bold ph-minus text-sm"></i></button>
                                            <span class="fx-stepper-qty">{{ $item->quantity }}</span>
                                            <button type="button" onclick="cartRowUpdate({{ $item->id }}, {{ $item->quantity + 1 }}, this)" aria-label="{{ $gu ? 'વધારો' : 'Increase' }}" @if($item->quantity >= $item->product->stock_quantity) disabled class="opacity-40" @endif><i class="ph-bold ph-plus text-sm"></i></button>
                                        </div>
                                        <span class="font-extrabold text-[15px] text-slate-900 dark:text-white">₹{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Summary -->
            <aside class="lg:sticky lg:top-36 space-y-4">
                <div class="fx-card p-5 sm:p-6">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base mb-4">{{ $gu ? 'ઓર્ડર સારાંશ' : 'Order summary' }}</h3>
                    <dl class="space-y-3 text-[13px]">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <dt>{{ __('messages.item_total') }}</dt><dd class="font-bold text-slate-900 dark:text-white">₹{{ number_format($subtotal, 2) }}</dd>
                        </div>
                        @if($savings > 0)
                            <div class="flex justify-between text-emerald-700 dark:text-emerald-400 font-semibold">
                                <dt>{{ $gu ? 'તમારી બચત' : 'Your savings' }}</dt><dd class="font-bold">−₹{{ number_format($savings, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <dt>{{ __('messages.delivery_fee') }}</dt>
                            <dd class="font-bold">@if($deliveryFee === 0)<span class="text-emerald-600 dark:text-emerald-400 uppercase">{{ __('messages.free') }}</span>@else<span class="text-slate-900 dark:text-white">₹40.00</span>@endif</dd>
                        </div>
                        <div class="flex justify-between items-baseline pt-3 border-t border-dashed border-slate-200 dark:border-slate-700">
                            <dt class="font-extrabold text-slate-900 dark:text-white">{{ __('messages.grand_total') }}</dt>
                            <dd class="text-xl font-extrabold text-slate-900 dark:text-white">₹{{ number_format($subtotal + $deliveryFee, 2) }}</dd>
                        </div>
                    </dl>
                    <p class="text-[11px] text-slate-400 mt-2">{{ $gu ? 'કૂપન ચેકઆઉટ પર લાગુ કરો.' : 'Apply coupons at checkout.' }}</p>
                    <a href="{{ route('checkout.index') }}" class="hidden lg:flex fx-btn fx-btn-primary fx-btn-lg w-full mt-5">{{ __('messages.proceed_to_checkout') }}<i class="ph-bold ph-arrow-right"></i></a>
                    <p class="hidden lg:flex mt-4 items-center justify-center gap-1.5 text-[11px] text-slate-400"><i class="ph-fill ph-shield-check text-emerald-500"></i>{{ $gu ? '૧૦૦% સુરક્ષિત ચેકઆઉટ' : '100% safe & secure checkout' }}</p>
                </div>
            </aside>
        </div>

        <!-- Mobile checkout bar -->
        <div class="lg:hidden h-20"></div>
        <div class="lg:hidden fixed inset-x-0 z-40 fx-above-tabbar">
            <div class="mx-3 mb-2 p-2.5 pl-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur border border-slate-200 dark:border-slate-700 shadow-2xl flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.grand_total') }}</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">₹{{ number_format($subtotal + $deliveryFee, 2) }}</p>
                </div>
                <a href="{{ route('checkout.index') }}" class="fx-btn fx-btn-primary !h-11 shrink-0">{{ $gu ? 'ચેકઆઉટ' : 'Checkout' }}<i class="ph-bold ph-arrow-right"></i></a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Update / remove through the shared AJAX helpers, then reload so totals stay server-calculated.
    function cartRowUpdate(id, qty, btn) {
        $(btn).closest('[data-cart-row]').addClass('opacity-60');
        updateCartItem(id, qty, btn).done(r => { if (r.success) location.reload(); }).fail(() => $(btn).closest('[data-cart-row]').removeClass('opacity-60'));
    }
    function cartRowRemove(id, btn) {
        $(btn).closest('[data-cart-row]').addClass('opacity-60');
        removeCartItem(id, btn).done(r => { if (r.success) location.reload(); }).fail(() => $(btn).closest('[data-cart-row]').removeClass('opacity-60'));
    }
</script>
@endpush
