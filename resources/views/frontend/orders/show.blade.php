@extends('frontend.layouts.app')

@section('title', 'Order #' . $order->order_number . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Order #{{ $order->order_number }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-receipt"></i>
            <span>{{ __('messages.download_invoice') }}</span>
        </a>
    </div>

    <!-- Live Status Tracker Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <h3 class="font-extrabold text-slate-900 text-sm">Delivery Status Timeline</h3>

        @php
            $statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered'];
            $currentIndex = array_search($order->order_status, $statuses);
            if ($order->order_status === 'cancelled') $currentIndex = -1;
        @endphp

        @if($order->order_status === 'cancelled')
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
                <i class="fa-solid fa-ban text-2xl text-rose-500"></i>
                <div>
                    <h5 class="font-bold text-sm">This order has been cancelled</h5>
                    <p class="text-slate-600 mt-0.5">Reason: {{ $order->cancellation_reason ?: 'Order cancelled upon request' }}</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-5 gap-2 text-center">
                @foreach($statuses as $idx => $st)
                    @php $isPassed = $currentIndex !== false && $idx <= $currentIndex; @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition-all {{ $isPassed ? 'bg-brand-600 text-white ring-4 ring-brand-100 shadow-md' : 'bg-slate-100 text-slate-400' }}">
                            @if($idx < $currentIndex)
                                <i class="fa-solid fa-check"></i>
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>
                        <span class="text-[11px] font-bold {{ $isPassed ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ ucfirst(str_replace('_', ' ', $st)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-3">
            <i class="fa-solid fa-bolt text-amber-500 text-lg"></i>
            <div>
                <span class="font-bold block">Delivery Window: {{ $order->delivery_slot }}</span>
                <span class="text-amber-700 text-[11px]">Estimated By: {{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d M Y, h:i A') : 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Items & Address Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Delivery Address -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 text-xs space-y-3">
            <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-rose-500"></i>
                <span>Delivery Address</span>
            </h4>
            <p class="font-bold text-slate-900 text-sm">{{ $order->customer_name }}</p>
            <p class="text-slate-600 leading-relaxed">{{ $order->delivery_address }}</p>
            <p class="text-slate-800 font-semibold">Phone: +91 {{ $order->customer_phone }}</p>
        </div>

        <!-- Payment Info -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 text-xs space-y-3">
            <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-credit-card text-emerald-600"></i>
                <span>Payment Information</span>
            </h4>
            <div class="flex justify-between">
                <span class="text-slate-500">Method:</span>
                <span class="font-bold uppercase text-slate-800">{{ $order->payment_method }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Status:</span>
                <span class="font-bold uppercase text-emerald-700">{{ $order->payment_status }}</span>
            </div>
            @if($order->transaction_id)
                <div class="flex justify-between font-mono">
                    <span class="text-slate-500">Txn Ref:</span>
                    <span class="font-bold text-slate-700">{{ $order->transaction_id }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Ordered Items -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <h4 class="font-extrabold text-slate-900 text-sm">Ordered Items</h4>

        <div class="divide-y divide-slate-100">
            @foreach($order->items as $item)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <img src="{{ $item->product ? $item->product->thumbnail_url : ($item->product_image ?: asset('images/default-product.png')) }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200">
                        <div>
                            <span class="font-bold text-slate-900 text-sm block">{{ $item->product_name_en }}</span>
                            <span class="text-brand-700 font-semibold">{{ $item->product_name_gu }}</span>
                            <span class="text-slate-400 block text-[11px]">{{ $item->product_unit }} &times; {{ $item->quantity }}</span>
                        </div>
                    </div>
                    <span class="font-extrabold text-slate-900 text-sm">₹{{ number_format($item->total_price, 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal</span>
                <span class="font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-bold">
                    <span>Discount</span>
                    <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-slate-600">
                <span>Delivery Fee</span>
                <span class="font-bold">{{ $order->delivery_charge == 0 ? 'FREE' : '₹' . number_format($order->delivery_charge, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-900 font-extrabold text-base pt-2 border-t border-slate-200">
                <span>Total Amount</span>
                <span class="text-brand-700">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
