@extends('frontend.layouts.app')

@section('title', __('messages.order_history') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.order_history') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track your past purchases and download tax invoices</p>
        </div>
        <a href="{{ route('order.track') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-crosshairs"></i>
            <span>Track Delivery</span>
        </a>
    </div>

    @forelse($orders as $order)
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <!-- Order Top Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 text-xs">
                <div>
                    <span class="font-extrabold text-brand-700 text-sm">#{{ $order->order_number }}</span>
                    <span class="text-slate-400 block text-[11px] mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($order->delivery_type === 'two_hours')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            ⚡ 2-Hour Express
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                            📅 Next Day Delivery
                        </span>
                    @endif

                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $order->status_badge_class }}">
                        {{ $order->localized_status }}
                    </span>
                </div>
            </div>

            <!-- Items -->
            <div class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-50 border border-slate-200 shrink-0">
                                <img src="{{ $item->product ? $item->product->thumbnail_url : ($item->product_image ?: asset('images/default-product.png')) }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $item->product_name_en }}</span>
                                <span class="text-brand-700 font-semibold text-[11px]">{{ $item->product_name_gu }}</span>
                                <span class="text-[10px] text-slate-400">{{ $item->product_unit }} &times; {{ $item->quantity }}</span>
                            </div>
                        </div>
                        <span class="font-bold text-slate-900">₹{{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Footer Details & Buttons -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">Total Amount:</span>
                    <span class="font-extrabold text-slate-900 text-base">₹{{ number_format($order->total_amount, 2) }}</span>
                    <span class="text-[10px] text-slate-400 block uppercase">via {{ $order->payment_method }} ({{ $order->payment_status }})</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Invoice</span>
                    </a>
                    <a href="{{ route('order.show', $order->order_number) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs shadow-sm transition-all">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="py-16 text-center bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-4">
            <i class="fa-solid fa-box-open text-5xl text-slate-300"></i>
            <h4 class="font-bold text-slate-700 text-base">You haven't placed any orders yet.</h4>
            <p class="text-xs text-slate-400">Order before 12:00 PM to receive your groceries in 2 hours!</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 bg-brand-600 text-white rounded-2xl text-xs font-bold shadow-md shadow-brand-500/20">
                {{ __('messages.start_shopping') }}
            </a>
        </div>
    @endforelse

    <div class="pt-4">
        {{ $orders->links() }}
    </div>

</div>
@endsection
