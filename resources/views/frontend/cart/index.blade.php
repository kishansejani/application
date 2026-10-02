@extends('frontend.layouts.app')

@section('title', __('messages.shopping_cart') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.shopping_cart') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Review items in your bag before proceeding to address & delivery</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-xs font-bold text-brand-700 hover:underline flex items-center gap-1">
            <i class="fa-solid fa-plus"></i>
            <span>Add More Items</span>
        </a>
    </div>

    @if($cartItems->isEmpty())
        <div class="py-20 text-center bg-white rounded-3xl border border-slate-200 shadow-sm p-8 max-w-lg mx-auto space-y-4">
            <div class="w-20 h-20 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-4xl mx-auto shadow-inner">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h3 class="text-xl font-extrabold text-slate-900">{{ __('messages.your_cart_is_empty') }}</h3>
            <p class="text-xs text-slate-400">Explore thousands of fresh fruits, vegetables, snacks, and grocery essentials.</p>
            <div class="pt-2">
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-bold shadow-lg shadow-brand-500/25 transition-all">
                    <span>{{ __('messages.start_shopping') }}</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Left 2 Cols: Cart Table & Delivery Progress -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Free Delivery Meter & 2-Hour Delivery Box -->
                @php
                    $threshold = 499;
                    $diff = max(0, $threshold - $subtotal);
                    $percent = min(100, round(($subtotal / $threshold) * 100));
                @endphp
                <div class="p-5 bg-gradient-to-r from-emerald-50 to-teal-50/60 rounded-3xl border border-emerald-200 space-y-3">
                    <div class="flex items-center justify-between text-xs font-bold text-emerald-900">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-truck-fast text-emerald-600"></i>
                            @if($diff > 0)
                                <span>Add <strong>₹{{ number_format($diff, 2) }}</strong> more for <strong>FREE Delivery!</strong></span>
                            @else
                                <span class="text-emerald-700">🎉 Congratulations! You unlocked <strong>FREE Delivery!</strong></span>
                            @endif
                        </span>
                        <span>{{ $percent }}%</span>
                    </div>
                    <div class="w-full bg-emerald-200/60 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-brand-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="pt-1 flex items-center justify-between text-[11px] text-emerald-800 font-medium">
                        <span>⚡ Delivery Slot: <strong>{{ app()->getLocale() === 'gu' ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</strong></span>
                        <span class="text-[10px] uppercase font-bold text-emerald-600">Cutoff 12 PM</span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-extrabold text-slate-900 text-sm">Cart Items ({{ $cartItems->sum('quantity') }})</h3>
                    </div>

                    <div class="divide-y divide-slate-100 p-6 space-y-4">
                        @foreach($cartItems as $item)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 first:pt-0">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 shrink-0">
                                        <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->localized_name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">
                                            <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-brand-600">
                                                {{ $item->product->localized_name }}
                                            </a>
                                        </h4>
                                        <span class="text-xs text-slate-400 font-medium">{{ $item->product->unit }}</span>
                                        <div class="text-xs font-bold text-slate-800 mt-1">
                                            ₹{{ number_format($item->product->effective_price, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-6">
                                    <!-- Quantity Controller -->
                                    <div class="flex items-center border border-slate-200 rounded-xl p-1 bg-slate-50">
                                        <button onclick="updateCartItem({{ $item->id }}, {{ $item->quantity - 1 }}); window.location.reload();" class="w-7 h-7 rounded-lg bg-white text-slate-700 font-bold hover:bg-slate-200 flex items-center justify-center text-xs">-</button>
                                        <span class="w-10 text-center font-bold text-xs text-slate-800">{{ $item->quantity }}</span>
                                        <button onclick="updateCartItem({{ $item->id }}, {{ $item->quantity + 1 }}); window.location.reload();" class="w-7 h-7 rounded-lg bg-white text-slate-700 font-bold hover:bg-slate-200 flex items-center justify-center text-xs">+</button>
                                    </div>

                                    <!-- Item Subtotal -->
                                    <div class="text-right min-w-[5rem]">
                                        <div class="font-extrabold text-slate-900 text-sm">
                                            ₹{{ number_format($item->subtotal, 2) }}
                                        </div>
                                        <button onclick="removeCartItem({{ $item->id }}); window.location.reload();" class="text-[11px] text-rose-500 font-semibold hover:underline mt-0.5">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Summary & Checkout Button -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 sticky top-28">
                    <h3 class="font-extrabold text-slate-900 text-base pb-4 border-b border-slate-100">Order Summary</h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('messages.item_total') }}</span>
                            <span class="font-bold text-slate-900">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('messages.delivery_fee') }}</span>
                            <span class="font-bold text-slate-900">
                                @if($subtotal >= 499)
                                    <span class="text-emerald-600 uppercase font-extrabold">{{ __('messages.free') }}</span>
                                @else
                                    <span>₹40.00</span>
                                @endif
                            </span>
                        </div>

                        <div class="flex justify-between text-slate-900 font-extrabold text-lg pt-4 border-t border-slate-200">
                            <span>{{ __('messages.grand_total') }}</span>
                            <span class="text-brand-700">₹{{ number_format($subtotal >= 499 ? $subtotal : $subtotal + 40, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="w-full py-4 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-extrabold shadow-xl shadow-brand-500/25 transition-all text-center flex items-center justify-center gap-2">
                        <span>{{ __('messages.proceed_to_checkout') }}</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <div class="space-y-2 text-[11px] text-slate-400 text-center">
                        <p class="flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-shield-check text-emerald-500"></i>
                            <span>100% Safe & Secure Checkout</span>
                        </p>
                        <p>Instant replacement if items are damaged</p>
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
