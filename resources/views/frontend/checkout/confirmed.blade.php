@extends('frontend.layouts.app')

@section('title', 'Order Confirmed - ' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">

    <!-- Celebration Header Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-10 text-center space-y-4">
        <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-4xl mx-auto shadow-inner animate-bounce">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            {{ __('messages.order_confirmed') }}
        </h1>

        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
            Thank you for ordering with FreshExpress Grocery! We are packing your items with the utmost hygiene.
        </p>

        <!-- Delivery Timing Pill -->
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
            <i class="fa-solid fa-bolt text-amber-500"></i>
            <span>{{ $order->delivery_slot }}</span>
        </div>
    </div>

    <!-- Order Summary Details Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pb-6 border-b border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px]">{{ __('messages.order_number') }}</span>
                <p class="font-extrabold text-brand-700 text-sm mt-0.5">{{ $order->order_number }}</p>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px]">Invoice Number</span>
                <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $order->invoice_number }}</p>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px]">Payment Method</span>
                <p class="font-bold uppercase text-slate-800 text-xs mt-0.5">{{ $order->payment_method }} ({{ $order->payment_status }})</p>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px]">Total Amount</span>
                <p class="font-extrabold text-slate-900 text-sm mt-0.5">₹{{ number_format($order->total_amount, 2) }}</p>
            </div>
        </div>

        <!-- Delivery Address Snapshot -->
        <div class="text-xs">
            <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-1">Delivering To:</h4>
            <p class="font-bold text-slate-900 text-sm">{{ $order->customer_name }} (+91 {{ $order->customer_phone }})</p>
            <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $order->delivery_address }}</p>
        </div>

        <!-- Items Snapshot -->
        <div class="space-y-3 pt-4 border-t border-slate-100">
            <h4 class="font-bold text-slate-400 uppercase tracking-wider text-xs">Items in this Delivery:</h4>
            <div class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-slate-900">{{ $item->product_name_en }}</span>
                            <span class="text-slate-400 font-medium">({{ $item->product_unit }} &times; {{ $item->quantity }})</span>
                        </div>
                        <span class="font-bold text-slate-900">₹{{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="w-full sm:w-auto px-5 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-receipt"></i>
                <span>{{ __('messages.download_invoice') }} / Print</span>
            </a>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('order.track', ['order_number' => $order->order_number]) }}" class="flex-1 sm:flex-none px-5 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-colors text-center">
                    Track Status
                </a>
                <a href="{{ route('products.index') }}" class="flex-1 sm:flex-none px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 transition-all text-center">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
