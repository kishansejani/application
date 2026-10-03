@extends('frontend.layouts.app')

@section('title', 'Order #' . $order->order_number . ' - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-4 sm:space-y-5">

    <div>
        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-[12px] font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 mb-2"><i class="ph-bold ph-arrow-left"></i>{{ __('messages.order_history') }}</a>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight break-all">#{{ $order->order_number }}</h1>
                    @include('frontend.partials.status-pill', ['order' => $order])
                </div>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'ઓર્ડર તારીખ' : 'Placed on' }} {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('order.track', ['order_number' => $order->order_number]) }}" class="fx-btn fx-btn-outline fx-btn-sm"><i class="ph ph-crosshair text-base"></i>{{ $gu ? 'ટ્રેક' : 'Track' }}</a>
                <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="fx-btn fx-btn-primary fx-btn-sm"><i class="ph ph-receipt text-base"></i>{{ __('messages.download_invoice') }}</a>
            </div>
        </div>
    </div>

    <section class="fx-card p-4 sm:p-6 space-y-5">
        <h3 class="font-extrabold text-slate-900 dark:text-white text-[15px]">{{ $gu ? 'ડિલિવરી સ્થિતિ' : 'Delivery status' }}</h3>
        @include('frontend.partials.order-timeline', ['order' => $order])
        <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-[13px]">
            <i class="ph-fill ph-lightning text-amber-500 text-lg shrink-0"></i>
            <div>
                <p class="font-bold text-amber-900 dark:text-amber-200">{{ $gu ? 'ડિલિવરી સમય' : 'Delivery window' }}: @include('frontend.partials.slot-text', ['order' => $order])</p>
                <p class="text-[12px] text-amber-800/80 dark:text-amber-200/70">{{ $gu ? 'અંદાજિત' : 'Estimated by' }}: {{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d M Y, h:i A') : 'N/A' }}</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
        <section class="fx-card p-4 sm:p-6 text-[13px] space-y-2">
            <h4 class="font-extrabold text-slate-900 dark:text-white text-[14px] flex items-center gap-2 mb-1"><i class="ph-fill ph-map-pin text-rose-500"></i>{{ $gu ? 'ડિલિવરી સરનામું' : 'Delivery address' }}</h4>
            <p class="font-bold text-slate-900 dark:text-white">{{ $order->customer_name }}</p>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">{{ $order->delivery_address }}</p>
            <p class="text-slate-700 dark:text-slate-200 font-semibold font-mono">+91 {{ $order->customer_phone }}</p>
        </section>
        <section class="fx-card p-4 sm:p-6 text-[13px]">
            <h4 class="font-extrabold text-slate-900 dark:text-white text-[14px] flex items-center gap-2 mb-3"><i class="ph-fill ph-credit-card text-emerald-600"></i>{{ $gu ? 'ચુકવણી માહિતી' : 'Payment information' }}</h4>
            <dl class="space-y-2">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">{{ $gu ? 'પદ્ધતિ' : 'Method' }}</dt><dd class="font-bold text-slate-900 dark:text-white">{{ \Lang::has('messages.payment_methods.' . $order->payment_method) ? __('messages.payment_methods.' . $order->payment_method) : strtoupper($order->payment_method) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">{{ $gu ? 'સ્થિતિ' : 'Status' }}</dt><dd class="font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ \Lang::has('messages.payment_statuses.' . $order->payment_status) ? __('messages.payment_statuses.' . $order->payment_status) : ucfirst($order->payment_status) }}</dd></div>
                @if($order->transaction_id)
                    <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-400">Txn ref</dt><dd class="font-mono font-bold text-slate-700 dark:text-slate-200 break-all text-right">{{ $order->transaction_id }}</dd></div>
                @endif
                @if($order->coupon_code)
                    <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">{{ $gu ? 'કૂપન' : 'Coupon' }}</dt><dd class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $order->coupon_code }}</dd></div>
                @endif
            </dl>
        </section>
    </div>

    <section class="fx-card p-4 sm:p-6">
        <h4 class="font-extrabold text-slate-900 dark:text-white text-[14px] mb-2">{{ $gu ? 'ઓર્ડર કરેલી વસ્તુઓ' : 'Ordered items' }} ({{ $order->items->sum('quantity') }})</h4>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($order->items as $item)
                <li class="py-3 flex items-center gap-3">
                    <img src="{{ $item->product ? $item->product->thumbnail_url : ($item->product_image ?: asset('images/default-product.png')) }}" alt="" class="w-14 h-14 rounded-xl object-cover bg-slate-100 dark:bg-slate-800 shrink-0" loading="lazy">
                    <div class="flex-1 min-w-0 text-[13px]">
                        @php
                            $itPrimary = $gu && $item->product_name_gu ? $item->product_name_gu : $item->product_name_en;
                            $itSecondary = $gu ? $item->product_name_en : $item->product_name_gu;
                        @endphp
                        @if($item->product)
                            <a href="{{ route('products.show', $item->product->slug) }}" class="font-bold text-slate-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400 line-clamp-1">{{ $itPrimary }}</a>
                        @else
                            <span class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $itPrimary }}</span>
                        @endif
                        <p class="text-brand-700 dark:text-brand-400 font-semibold text-[12px] line-clamp-1">{{ $itSecondary }}</p>
                        <p class="text-[12px] text-slate-400">{{ $item->product_unit }} × {{ $item->quantity }} · ₹{{ number_format($item->unit_price, 2) }}</p>
                    </div>
                    <span class="font-extrabold text-[14px] text-slate-900 dark:text-white shrink-0">₹{{ number_format($item->total_price, 2) }}</span>
                </li>
            @endforeach
        </ul>
        <dl class="mt-2 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2 text-[13px] sm:max-w-xs sm:ml-auto">
            <div class="flex justify-between text-slate-600 dark:text-slate-300"><dt>{{ $gu ? 'પેટા સરવાળો' : 'Subtotal' }}</dt><dd class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->subtotal, 2) }}</dd></div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-700 dark:text-emerald-400 font-semibold"><dt>{{ __('messages.discount') }}</dt><dd class="font-bold">-₹{{ number_format($order->discount_amount, 2) }}</dd></div>
            @endif
            <div class="flex justify-between text-slate-600 dark:text-slate-300"><dt>{{ __('messages.delivery_fee') }}</dt><dd class="font-bold text-slate-900 dark:text-white">{{ $order->delivery_charge == 0 ? __('messages.free') : '₹' . number_format($order->delivery_charge, 2) }}</dd></div>
            <div class="flex justify-between items-baseline pt-2 border-t border-dashed border-slate-200 dark:border-slate-700"><dt class="font-extrabold text-slate-900 dark:text-white">{{ __('messages.grand_total') }}</dt><dd class="text-lg font-extrabold text-slate-900 dark:text-white">₹{{ number_format($order->total_amount, 2) }}</dd></div>
        </dl>
    </section>
</div>
@endsection
