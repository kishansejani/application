@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Customer Orders</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage incoming order dispatches, 2-hour express deliveries, and invoices.</p>
        </div>
    </div>

    <!-- Stat Pipeline Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Card 1: All Orders -->
        <a href="{{ route('admin.orders.index') }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ !request('status') && !request('delivery_type') ? 'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-lg border border-slate-700/60' : 'bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700 hover:border-slate-300 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider {{ !request('status') && !request('delivery_type') ? 'text-slate-300' : 'text-slate-500 dark:text-slate-400' }}">All Orders</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ !request('status') && !request('delivery_type') ? 'bg-white/10 text-indigo-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                    <i class="fa-solid fa-cart-flatbed-suitcase"></i>
                </div>
            </div>
            <div class="text-2xl font-black mt-2 {{ !request('status') && !request('delivery_type') ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['total'] }}</div>
            <span class="text-[10px] font-bold text-slate-400 block mt-1">● Total Orders</span>
        </a>

        <!-- Card 2: 2-Hr Express -->
        <a href="{{ route('admin.orders.index', ['delivery_type' => 'two_hours']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('delivery_type') == 'two_hours' ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg border border-amber-400' : 'bg-gradient-to-br from-white via-white to-amber-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">⚡ 2-Hr Express</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('delivery_type') == 'two_hours' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400' }}">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>
            <div class="text-2xl font-black mt-2 {{ request('delivery_type') == 'two_hours' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['two_hour_express'] }}</div>
            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block mt-1">★ Priority</span>
        </a>

        <!-- Card 3: Pending -->
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('status') == 'pending' ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg border border-amber-400' : 'bg-gradient-to-br from-white via-white to-amber-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pending</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('status') == 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400' }}">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="text-2xl font-black mt-2 {{ request('status') == 'pending' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['pending'] }}</div>
            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block mt-1">● Queued</span>
        </a>

        <!-- Card 4: Processing -->
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('status') == 'processing' ? 'bg-gradient-to-br from-indigo-600 to-blue-700 text-white shadow-lg border border-indigo-500' : 'bg-gradient-to-br from-white via-white to-indigo-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Processing</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('status') == 'processing' ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' }}">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
            <div class="text-2xl font-black mt-2 {{ request('status') == 'processing' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['processing'] }}</div>
            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block mt-1">● Packing</span>
        </a>

        <!-- Card 5: Out for Delivery -->
        <a href="{{ route('admin.orders.index', ['status' => 'out_for_delivery']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('status') == 'out_for_delivery' ? 'bg-gradient-to-br from-purple-600 to-indigo-700 text-white shadow-lg border border-purple-500' : 'bg-gradient-to-br from-white via-white to-purple-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-purple-950/30 border border-purple-200/80 dark:border-purple-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-400">On The Road</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('status') == 'out_for_delivery' ? 'bg-white/20 text-white' : 'bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400' }}">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="text-2xl font-black mt-2 {{ request('status') == 'out_for_delivery' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['out_for_delivery'] }}</div>
            <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 block mt-1">● Dispatched</span>
        </a>

        <!-- Card 6: Delivered -->
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('status') == 'delivered' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg border border-emerald-500' : 'bg-gradient-to-br from-white via-white to-emerald-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Delivered</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('status') == 'delivered' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400' }}">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="text-2xl font-black mt-2 {{ request('status') == 'delivered' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['delivered'] }}</div>
            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block mt-1">● Completed</span>
        </a>
    </div>

    <!-- Orders DataTable Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="ordersTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3.5 rounded-l-2xl">Order Info</th>
                        <th class="px-4 py-3.5">Customer & Phone</th>
                        <th class="px-4 py-3.5">Delivery Slot</th>
                        <th class="px-4 py-3.5">Items</th>
                        <th class="px-4 py-3.5">Amount & Payment</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @foreach($orders as $order)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-mono">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                @if($order->invoice_number)
                                    <span class="inline-block text-[10px] font-mono text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-lg mt-1 font-semibold">{{ $order->invoice_number }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $order->customer_name }}</div>
                                <a href="tel:{{ $order->customer_phone }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-phone text-[10px]"></i>
                                    <span>+91 {{ $order->customer_phone }}</span>
                                </a>
                                <div class="text-[10px] text-slate-400 truncate max-w-xs mt-0.5">{{ $order->delivery_city }} - {{ $order->delivery_pincode }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($order->delivery_type === 'two_hours')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                        ⚡ 2-HOUR EXPRESS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold bg-blue-100 dark:bg-blue-950/60 text-blue-900 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                        📅 NEXT DAY
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-semibold mt-1">{{ $order->delivery_slot }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">
                                    {{ $order->items->count() }} items
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">₹{{ number_format($order->total_amount, 2) }}</div>
                                <span class="inline-block text-[10px] font-bold uppercase mt-0.5 px-2 py-0.5 rounded-lg {{ $order->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                                    {{ $order->payment_method }} • {{ $order->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold border {{ $order->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="View Order Details">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.invoices.show', $order) }}" class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Print Invoice">
                                        <i class="fa-solid fa-receipt text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            order: [[0, 'desc']]
        });
    });
</script>
@endpush
