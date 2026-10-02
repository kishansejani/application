@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Overview & Analytics')
@section('page-subtitle', 'Real-time performance metrics and order fulfillment status')

@section('action-buttons')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        <span>Add New Product</span>
    </a>
    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-truck"></i>
        <span>View Orders</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- 2-Hour Delivery Cutoff Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-700/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center text-3xl shrink-0">
                ⚡
            </div>
            <div>
                <h3 class="font-bold text-lg">Express 2-Hour vs Next-Day Dispatch Active</h3>
                <p class="text-xs text-emerald-100 mt-0.5">Orders received before 12:00 PM are queued for 2-hour immediate delivery. Orders received after 12:00 PM are scheduled for next-day morning slots.</p>
            </div>
        </div>
        <div class="bg-white/15 px-4 py-2 rounded-xl text-xs font-semibold backdrop-blur shrink-0">
            Cutoff Standard: 12:00 PM Noon
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Revenue</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">₹{{ number_format($totalRevenue, 2) }}</h3>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 mt-1">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <span>₹{{ number_format($todayRevenue, 2) }} today</span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
        </div>

        <!-- Card 2: Total Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalOrders }}</h3>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 mt-1">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ $pendingOrders }} pending fulfillment</span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
        </div>

        <!-- Card 3: Total Products & Low Stock Alert -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Products</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalProducts }}</h3>
                <a href="{{ route('admin.stock.index', ['filter' => 'low']) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 hover:underline mt-1">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $lowStockProducts->count() }} low stock items</span>
                </a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <!-- Card 4: Registered Customers -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Customers</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalCustomers }}</h3>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 mt-1">
                    <i class="fa-solid fa-shield-check"></i>
                    <span>Mobile OTP Verified</span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Charts Section: 7-Day Revenue & Order Status Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Trend Line Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900">7-Day Sales Trend</h4>
                    <p class="text-xs text-slate-400">Daily revenue across all channels</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">Last 7 Days</span>
            </div>
            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Order Status Breakdown Donut -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900">Order Status Distribution</h4>
                    <p class="text-xs text-slate-400">Current pipeline breakdown</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Orders & Urgent Low Stock Refills -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Recent Orders Table (2 Columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-slate-900">Recent Customer Orders</h4>
                    <p class="text-xs text-slate-400">Latest incoming delivery requests</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">View All Orders &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <th class="p-3.5">Order #</th>
                            <th class="p-3.5">Customer</th>
                            <th class="p-3.5">Delivery Slot</th>
                            <th class="p-3.5">Amount</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-brand-700 hover:underline">
                                        {{ $order->order_number }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $order->created_at->format('d M, h:i A') }}</span>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-semibold text-slate-800">{{ $order->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->customer_phone }}</div>
                                </td>
                                <td class="p-3.5">
                                    @if($order->delivery_type === 'two_hours')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            ⚡ 2-Hour Express
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            📅 Next Day
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 font-bold text-slate-900">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $order->status_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.invoices.show', $order) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Invoice">
                                            <i class="fa-solid fa-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">No orders placed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alerts Panel (1 Column) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                        <span>Low Stock Inventory</span>
                    </h4>
                    <p class="text-xs text-slate-400">Items needing immediate refill</p>
                </div>
                <a href="{{ route('admin.stock.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Stock Sheet &rarr;</a>
            </div>

            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-96">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-3 rounded-xl border border-rose-100 bg-rose-50/40">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->thumbnail_url }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 leading-tight">{{ $product->localized_name }}</h5>
                                <p class="text-[11px] text-slate-500">{{ $product->unit }} • ₹{{ number_format($product->effective_price, 2) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-rose-600 text-white">
                                {{ $product->stock_quantity }} Left
                            </span>
                            <a href="{{ route('admin.stock.index') }}" class="block text-[10px] text-brand-600 font-bold hover:underline mt-1">+ Refill</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400">
                        <i class="fa-solid fa-circle-check text-3xl text-emerald-400 mb-2"></i>
                        <p class="text-xs font-medium">All products are well stocked!</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesTrend['labels']) !!},
            datasets: [{
                label: 'Revenue (₹)',
                data: {!! json_encode($salesTrend['data']) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: (val) => '₹' + val }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Order Status Donut Chart
    const statusCtx = document.getElementById('statusDonutChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Processing', 'Out for Delivery', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $statusCounts['pending'] }},
                    {{ $statusCounts['confirmed'] }},
                    {{ $statusCounts['processing'] }},
                    {{ $statusCounts['out_for_delivery'] }},
                    {{ $statusCounts['delivered'] }},
                    {{ $statusCounts['cancelled'] }},
                ],
                backgroundColor: [
                    '#f59e0b', // amber
                    '#3b82f6', // blue
                    '#6366f1', // indigo
                    '#a855f7', // purple
                    '#10b981', // emerald
                    '#f43f5e', // rose
                ],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, font: { size: 10 } }
                }
            },
            cutout: '70%'
        }
    });
</script>
@endpush
