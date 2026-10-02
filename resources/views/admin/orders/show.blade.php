@extends('admin.layouts.admin')

@section('title', 'Order #' . $order->order_number)
@section('page-title', 'Order Details #' . $order->order_number)
@section('page-subtitle', 'Placed on ' . $order->created_at->format('d M Y, h:i A'))

@section('action-buttons')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.invoices.show', $order) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-receipt"></i>
        <span>View / Print Invoice</span>
    </a>
    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Orders</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Order Timeline / Workflow Status Tracker -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h4 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-6">Fulfillment Workflow</h4>

        @php
            $statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered'];
            $currentIndex = array_search($order->order_status, $statuses);
            if ($order->order_status === 'cancelled') $currentIndex = -1;
        @endphp

        @if($order->order_status === 'cancelled')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-2xl text-rose-500"></i>
                <div>
                    <h5 class="font-bold text-sm">Order Cancelled</h5>
                    <p class="text-xs text-rose-600 mt-0.5">Reason: {{ $order->cancellation_reason ?: 'Cancelled by administrator' }}</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-5 gap-2 text-center relative">
                @foreach($statuses as $idx => $st)
                    @php $isPassed = $currentIndex !== false && $idx <= $currentIndex; @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 shadow-sm transition-all {{ $isPassed ? 'bg-brand-600 text-white shadow-brand-500/25 ring-4 ring-brand-100' : 'bg-slate-100 text-slate-400' }}">
                            @if($idx < $currentIndex)
                                <i class="fa-solid fa-check"></i>
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>
                        <span class="text-xs font-bold {{ $isPassed ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ ucfirst(str_replace('_', ' ', $st)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Main Grid (Left: Items & Delivery Details, Right: Controls & Customer) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Columns: Items and Delivery Details -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Delivery Timing Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Delivery Scheduling Rule</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Calculated based on 12:00 PM cutoff</p>
                    </div>
                    <div>
                        @if($order->delivery_type === 'two_hours')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shadow-sm">
                                ⚡ 2-HOUR EXPRESS DISPATCH
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-blue-100 text-blue-900 border border-blue-300 shadow-sm">
                                📅 NEXT-DAY MORNING DELIVERY
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block mb-1">Target Delivery Window</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $order->delivery_slot }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block mb-1">Estimated By</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d M Y, h:i A') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Ordered Items List -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="font-bold text-slate-900 text-sm mb-4">Ordered Items ({{ $order->items->count() }})</h4>

                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <div class="py-3.5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shrink-0">
                                    <img src="{{ $item->product ? $item->product->thumbnail_url : ($item->product_image ?: asset('images/default-product.png')) }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-900 text-xs">{{ $item->product_name_en }}</h5>
                                    <p class="text-xs text-brand-700 font-semibold">{{ $item->product_name_gu }}</p>
                                    <span class="text-[11px] text-slate-400">{{ $item->product_unit }} • ₹{{ number_format($item->unit_price, 2) }} each</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-500">Qty: {{ $item->quantity }}</span>
                                <div class="font-extrabold text-slate-900 text-sm mt-0.5">₹{{ number_format($item->total_price, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Breakdown -->
                <div class="mt-6 pt-4 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600 font-semibold">
                            <span>Coupon Discount ({{ $order->coupon_code }})</span>
                            <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Fee</span>
                        <span class="font-bold">{{ $order->delivery_charge == 0 ? 'FREE' : '₹' . number_format($order->delivery_charge, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-900 font-extrabold text-base pt-3 border-t border-slate-200">
                        <span>Total Paid / Payable</span>
                        <span class="text-brand-700">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 1 Column: Customer Details, Controls, Address & Notes -->
        <div class="space-y-6">

            <!-- Customer & Delivery Address Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user text-brand-600"></i>
                    <span>Customer & Address</span>
                </h4>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Customer Name</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $order->customer_name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Contact Number</span>
                        <a href="tel:{{ $order->customer_phone }}" class="font-bold text-brand-600 hover:underline flex items-center gap-1.5 text-sm">
                            <i class="fa-solid fa-phone"></i>
                            <span>+91 {{ $order->customer_phone }}</span>
                        </a>
                    </div>

                    @if($order->customer_email)
                        <div>
                            <span class="text-slate-400 block mb-0.5">Email</span>
                            <span class="font-medium text-slate-700">{{ $order->customer_email }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-slate-400 block mb-1">Delivery Address</span>
                        <p class="font-medium text-slate-800 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                            {{ $order->delivery_address }}
                        </p>
                    </div>

                    @if($order->delivery_lat && $order->delivery_lng)
                        <div class="pt-2">
                            <a href="https://www.google.com/maps?q={{ $order->delivery_lat }},{{ $order->delivery_lng }}" target="_blank" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold flex items-center justify-center gap-2 transition-colors">
                                <i class="fa-solid fa-location-dot text-rose-500"></i>
                                <span>Open Pin in Google Maps</span>
                            </a>
                        </div>
                    @endif

                    @if($order->notes)
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-slate-400 block mb-1">Customer Delivery Notes</span>
                            <p class="italic text-slate-600 bg-amber-50/50 p-2.5 rounded-xl border border-amber-100">
                                "{{ $order->notes }}"
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Update Order Status Form -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-indigo-600"></i>
                    <span>Update Order Status</span>
                </h4>

                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status</label>
                        <select name="order_status" id="orderStatusSelect" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing / Packing</option>
                            <option value="out_for_delivery" {{ $order->order_status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                            <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div id="cancelReasonBox" class="{{ $order->order_status === 'cancelled' ? '' : 'hidden' }}">
                        <label class="block text-xs font-bold text-rose-700 uppercase mb-1">Cancellation Reason</label>
                        <input type="text" name="cancellation_reason" value="{{ $order->cancellation_reason }}" placeholder="Customer requested, out of stock..." class="w-full px-3 py-2 border border-rose-200 rounded-xl text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Admin Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ $order->notes }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 transition-all">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Update Payment Status Form -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-emerald-600"></i>
                    <span>Payment Information</span>
                </h4>

                <form action="{{ route('admin.orders.update_payment', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Payment Method:</span>
                            <span class="font-bold uppercase text-slate-800">{{ $order->payment_method }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Payment Status</label>
                        <select name="payment_status" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Transaction ID</label>
                        <input type="text" name="transaction_id" value="{{ $order->transaction_id }}" placeholder="UPI/Gateway Ref ID" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
                        Update Payment
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    $('#orderStatusSelect').on('change', function() {
        if ($(this).val() === 'cancelled') {
            $('#cancelReasonBox').removeClass('hidden');
        } else {
            $('#cancelReasonBox').addClass('hidden');
        }
    });
</script>
@endpush
