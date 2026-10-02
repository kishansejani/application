@extends('admin.layouts.admin')

@section('title', 'Manage Orders')
@section('page-title', 'Customer Orders')
@section('page-subtitle', 'Manage incoming order dispatches, 2-hour express delivery, and status workflows')

@section('content')
<div class="space-y-6">

    <!-- Stat Pipeline Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <a href="{{ route('admin.orders.index') }}" class="p-4 rounded-2xl border {{ !request('status') && !request('delivery_type') ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }} transition-all">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">All Orders</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['total'] }}</h4>
        </a>

        <a href="{{ route('admin.orders.index', ['delivery_type' => 'two_hours']) }}" class="p-4 rounded-2xl border {{ request('delivery_type') == 'two_hours' ? 'bg-amber-500 text-white border-amber-500 shadow-md' : 'bg-white border-slate-200 text-slate-700 hover:bg-amber-50' }} transition-all">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">⚡ 2-Hr Express</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['two_hour_express'] }} Active</h4>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="p-4 rounded-2xl border {{ request('status') == 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-md' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }} transition-all">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Pending</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['pending'] }}</h4>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="p-4 rounded-2xl border {{ request('status') == 'processing' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }} transition-all">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Packing / Prep</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['processing'] }}</h4>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'out_for_delivery']) }}" class="p-4 rounded-2xl border {{ request('status') == 'out_for_delivery' ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }} transition-all">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Out for Delivery</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['out_for_delivery'] }}</h4>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="p-4 rounded-2xl border {{ request('status') == 'delivered' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }} transition-all">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Delivered</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['delivered'] }}</h4>
        </a>
    </div>

    <!-- Orders DataTable Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="overflow-x-auto">
            <table id="ordersTable" class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <th class="p-3.5">Order Info</th>
                        <th class="p-3.5">Customer & Phone</th>
                        <th class="p-3.5">Delivery Slot / Rule</th>
                        <th class="p-3.5">Items Count</th>
                        <th class="p-3.5">Amount & Payment</th>
                        <th class="p-3.5">Order Status</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-brand-700 hover:underline text-sm">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                @if($order->invoice_number)
                                    <span class="inline-block text-[10px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded mt-1">{{ $order->invoice_number }}</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-900">{{ $order->customer_name }}</div>
                                <a href="tel:{{ $order->customer_phone }}" class="text-xs text-brand-600 font-semibold hover:underline flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-phone text-[10px]"></i>
                                    <span>+91 {{ $order->customer_phone }}</span>
                                </a>
                                <div class="text-[10px] text-slate-400 truncate max-w-xs mt-0.5">{{ $order->delivery_city }} - {{ $order->delivery_pincode }}</div>
                            </td>
                            <td class="p-3.5">
                                @if($order->delivery_type === 'two_hours')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                        ⚡ 2-HOUR EXPRESS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-900 border border-blue-300">
                                        📅 NEXT DAY MORNING
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-600 font-semibold mt-1">{{ $order->delivery_slot }}</div>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                    {{ $order->items->count() }} items
                                </span>
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-900 text-sm">₹{{ number_format($order->total_amount, 2) }}</div>
                                <span class="inline-block text-[10px] font-bold uppercase mt-0.5 px-2 py-0.5 rounded {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $order->payment_method }} • {{ $order->payment_status }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $order->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Manage Order Workflow">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.invoices.show', $order) }}" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Invoice">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">No orders found matching criteria.</td>
                        </tr>
                    @endforelse
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
            responsive: true,
            pageLength: 15,
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', className: 'px-3 py-1.5 text-xs bg-slate-100 rounded-lg mr-2 font-semibold' },
                { extend: 'csv', className: 'px-3 py-1.5 text-xs bg-slate-100 rounded-lg mr-2 font-semibold' },
                { extend: 'print', className: 'px-3 py-1.5 text-xs bg-slate-100 rounded-lg font-semibold' }
            ],
            order: [[0, 'desc']]
        });
    });
</script>
@endpush
