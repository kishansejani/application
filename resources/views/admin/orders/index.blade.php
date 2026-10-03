@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('content')
@php
    $statusMeta = [
        'pending'          => ['Pending',          'badge-warning'],
        'confirmed'        => ['Confirmed',        'badge-info'],
        'processing'       => ['Processing',       'badge-violet'],
        'out_for_delivery' => ['Out for delivery', 'badge-violet'],
        'delivered'        => ['Delivered',        'badge-success'],
        'cancelled'        => ['Cancelled',        'badge-danger'],
    ];
    $paymentMeta = [
        'pending'  => ['Pending',  'badge-warning'],
        'paid'     => ['Paid',     'badge-success'],
        'failed'   => ['Failed',   'badge-danger'],
        'refunded' => ['Refunded', 'badge-neutral'],
    ];
    $methodLabel = fn ($m) => match (strtolower((string) $m)) {
        'cod' => 'Cash on delivery', 'upi' => 'UPI', 'card' => 'Card', 'netbanking' => 'Net banking',
        default => ucfirst((string) $m),
    };
    $noFilter = !request('status') && !request('delivery_type') && !request('payment_status') && !request('search');
@endphp

    <x-admin.page-header title="Orders" subtitle="Track incoming orders, 2-hour express deliveries, payments and invoices." icon="shopping-cart-simple">
        @if(!$noFilter)
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline"><i class="ph ph-x-circle"></i> Clear filters</a>
        @endif
    </x-admin.page-header>

    <div class="stat-grid cols-6">
        <x-admin.stat-card label="All orders" :value="$stats['total']" icon="package" tone="slate"
            :href="route('admin.orders.index')" :active="$noFilter" meta="Lifetime orders" />
        <x-admin.stat-card label="2-hour express" :value="$stats['two_hour_express']" icon="lightning" tone="orange"
            :href="route('admin.orders.index', ['delivery_type' => 'two_hours'])" :active="request('delivery_type') === 'two_hours' && !request('status')" meta="Open priority orders" />
        <x-admin.stat-card label="Pending" :value="$stats['pending']" icon="clock" tone="amber"
            :href="route('admin.orders.index', ['status' => 'pending'])" :active="request('status') === 'pending'" meta="Awaiting confirmation" />
        <x-admin.stat-card label="Processing" :value="$stats['processing']" icon="package" tone="blue"
            :href="route('admin.orders.index', ['status' => 'processing'])" :active="request('status') === 'processing'" meta="Confirmed and packing" />
        <x-admin.stat-card label="Out for delivery" :value="$stats['out_for_delivery']" icon="truck" tone="violet"
            :href="route('admin.orders.index', ['status' => 'out_for_delivery'])" :active="request('status') === 'out_for_delivery'" meta="On the road" />
        <x-admin.stat-card label="Delivered" :value="$stats['delivered']" icon="check-circle" tone="emerald"
            :href="route('admin.orders.index', ['status' => 'delivered'])" :active="request('status') === 'delivered'" meta="Completed orders" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5" data-no-loading>
                <div class="relative flex-1 min-w-[12rem] sm:flex-none sm:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Order no., customer or phone" class="form-control !pl-9" aria-label="Search orders">
                </div>
                <select name="status" onchange="this.form.submit()" class="form-select w-auto min-w-[10rem]" aria-label="Order status">
                    <option value="">All statuses</option>
                    @foreach($statusMeta as $key => [$label])
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="delivery_type" onchange="this.form.submit()" class="form-select w-auto min-w-[10rem]" aria-label="Delivery type">
                    <option value="">All delivery types</option>
                    <option value="two_hours" {{ request('delivery_type') === 'two_hours' ? 'selected' : '' }}>2-hour express</option>
                    <option value="next_day" {{ request('delivery_type') === 'next_day' ? 'selected' : '' }}>Next-day morning</option>
                </select>
                <select name="payment_status" onchange="this.form.submit()" class="form-select w-auto min-w-[10rem]" aria-label="Payment status">
                    <option value="">All payments</option>
                    @foreach($paymentMeta as $key => [$label])
                        <option value="{{ $key }}" {{ request('payment_status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm"><i class="ph ph-funnel"></i> Apply</button>
                @if(!$noFilter)
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm text-rose-600"><i class="ph ph-x-circle"></i> Clear filters</a>
                @endif
            </form>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $orders->count() }} {{ \Illuminate\Support\Str::plural('order', $orders->count()) }}</span>
        </div>

        <table id="ordersTable" class="w-full" data-export-title="Orders">
            <thead>
                <tr>
                    <th>Order no.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="export-only">Phone</th>
                    <th>Items</th>
                    <th>Delivery type</th>
                    <th class="export-only">Payment method</th>
                    <th>Payment status</th>
                    <th>Order status</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    @php
                        [$stLabel, $stClass] = $statusMeta[$order->order_status] ?? [ucfirst(str_replace('_', ' ', $order->order_status)), 'badge-neutral'];
                        [$payLabel, $payClass] = $paymentMeta[$order->payment_status] ?? [ucfirst($order->payment_status), 'badge-neutral'];
                        $itemCount = $order->items->count();
                        $isExpress = $order->delivery_type === 'two_hours';
                    @endphp
                    <tr>
                        <td data-export="{{ $order->order_number }}">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-[12.5px] text-slate-900 dark:text-white hover:underline whitespace-nowrap">{{ $order->order_number }}</a>
                            @if($order->invoice_number)
                                <span class="block font-mono text-[10.5px] text-slate-400 mt-0.5">{{ $order->invoice_number }}</span>
                            @endif
                        </td>
                        <td data-order="{{ $order->created_at->timestamp }}" data-export="{{ $order->created_at->format('d M Y, h:i A') }}" class="whitespace-nowrap">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $order->created_at->format('d M Y') }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $order->created_at->format('h:i A') }}</span>
                        </td>
                        <td data-export="{{ $order->customer_name }}">
                            <div class="min-w-[10rem]">
                                <span class="block font-bold text-slate-900 dark:text-white leading-snug">{{ $order->customer_name }}</span>
                                <a href="tel:{{ $order->customer_phone }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"><i class="ph ph-phone"></i> +91 {{ $order->customer_phone }}</a>
                                @if($order->delivery_city || $order->delivery_pincode)
                                    <span class="block text-[11px] text-slate-400">{{ trim($order->delivery_city.' '.$order->delivery_pincode) }}</span>
                                @endif
                            </div>
                        </td>
                        <td>+91 {{ $order->customer_phone }}</td>
                        <td data-order="{{ $itemCount }}" data-export="{{ $itemCount }}">
                            <span class="badge badge-neutral">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span>
                        </td>
                        <td data-export="{{ $isExpress ? '2-hour express' : 'Next-day morning' }}">
                            @if($isExpress)
                                <span class="badge badge-warning"><i class="ph-fill ph-lightning"></i> 2-hour express</span>
                            @else
                                <span class="badge badge-info"><i class="ph ph-calendar-blank"></i> Next day</span>
                            @endif
                            <span class="block text-[11px] text-slate-400 mt-1 max-w-[11rem] truncate" title="{{ $order->delivery_slot }}">{{ $order->delivery_slot }}</span>
                        </td>
                        <td>{{ $methodLabel($order->payment_method) }}</td>
                        <td data-export="{{ $payLabel }}">
                            <span class="badge {{ $payClass }} badge-dot">{{ $payLabel }}</span>
                            <span class="block text-[11px] text-slate-400 mt-1 uppercase tracking-wide">{{ $order->payment_method }}</span>
                        </td>
                        <td data-export="{{ $stLabel }}">
                            <span class="badge {{ $stClass }} badge-dot whitespace-nowrap">{{ $stLabel }}</span>
                        </td>
                        <td class="text-right whitespace-nowrap" data-order="{{ $order->total_amount }}" data-export="₹{{ number_format($order->total_amount, 2) }}">
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($order->total_amount, 2) }}</span>
                            @if($order->discount_amount > 0)
                                <span class="block text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">−₹{{ number_format($order->discount_amount, 2) }} coupon</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="act-btn is-edit" title="View order"><i class="ph ph-eye"></i></a>
                                <a href="{{ route('admin.invoices.show', $order) }}" class="act-btn" title="Invoice"><i class="ph ph-receipt"></i></a>
                                <a href="{{ route('admin.invoices.print', ['order' => $order, 'autoprint' => 1]) }}" target="_blank" class="act-btn" title="Print invoice"><i class="ph ph-printer"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#ordersTable').DataTable({
            order: [[1, 'desc']],
            columnDefs: [
                { targets: [3, 6], visible: false },       // Phone, payment method: export-only
                { targets: 0, responsivePriority: 1 },
                { targets: 2, responsivePriority: 2 },
                { targets: 8, responsivePriority: 3 },
                { targets: 9, responsivePriority: 4 },
                { targets: 10, responsivePriority: 5 },
                { targets: 1, responsivePriority: 6 },
                { targets: 7, responsivePriority: 7 },
                { targets: 5, responsivePriority: 8 },
                { targets: 4, responsivePriority: 9 }
            ]
        });
    });
</script>
@endpush
