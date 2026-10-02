@extends('frontend.layouts.app')

@section('title', 'Track Order Delivery - ' . __('messages.store_name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">

    <div class="text-center space-y-2">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Live Order Delivery Tracking</h1>
        <p class="text-xs text-slate-500">Enter your order reference number to view real-time fulfillment updates</p>
    </div>

    <!-- Search Box -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('order.track') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="order_number" value="{{ request('order_number') }}" required placeholder="Enter Order Number (e.g. ORD-68DF09A)" class="w-full sm:flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-mono uppercase font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-brand-500/20 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Track Status</span>
            </button>
        </form>
    </div>

    @if($order)
        <!-- Live Status Timeline Result -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-8 space-y-6 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 text-xs">
                <div>
                    <span class="font-extrabold text-brand-700 text-sm">#{{ $order->order_number }}</span>
                    <span class="text-slate-400 block text-[11px] mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>

                <div class="flex items-center gap-2">
                    @if($order->delivery_type === 'two_hours')
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                            ⚡ 2-Hour Express
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-blue-900 border border-blue-300">
                            📅 Next Day
                        </span>
                    @endif
                </div>
            </div>

            <!-- Visual Stepper -->
            @php
                $statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered'];
                $currentIndex = array_search($order->order_status, $statuses);
                if ($order->order_status === 'cancelled') $currentIndex = -1;
            @endphp

            @if($order->order_status === 'cancelled')
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-ban text-2xl text-rose-500"></i>
                    <div>
                        <h5 class="font-bold text-sm">Order Cancelled</h5>
                        <p class="text-xs text-rose-600 mt-0.5">{{ $order->cancellation_reason ?: 'Cancelled' }}</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-5 gap-2 text-center py-4">
                    @foreach($statuses as $idx => $st)
                        @php $isPassed = $currentIndex !== false && $idx <= $currentIndex; @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition-all {{ $isPassed ? 'bg-brand-600 text-white ring-4 ring-brand-100 shadow-md' : 'bg-slate-100 text-slate-400' }}">
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

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-400">Target Delivery Slot:</span>
                    <span class="font-bold text-slate-900">{{ $order->delivery_slot }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Recipient Name:</span>
                    <span class="font-bold text-slate-900">{{ $order->customer_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Delivery Address:</span>
                    <span class="font-medium text-slate-700 truncate max-w-xs">{{ $order->delivery_address }}</span>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <a href="{{ route('order.show', $order->order_number) }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs shadow transition-all">
                    View Full Order Details &rarr;
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
