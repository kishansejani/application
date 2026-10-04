@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', Auth::user()->name ?? 'Admin')[0];
    $u = Auth::user();
    $statusMeta = [
        'pending'          => ['Pending',          '#f59e0b', 'badge-warning'],
        'confirmed'        => ['Confirmed',        '#3b82f6', 'badge-info'],
        'processing'       => ['Processing',       '#6366f1', 'badge-violet'],
        'out_for_delivery' => ['Out for delivery', '#a855f7', 'badge-violet'],
        'delivered'        => ['Delivered',        '#10b981', 'badge-success'],
        'cancelled'        => ['Cancelled',        '#f43f5e', 'badge-danger'],
    ];
    $statusTotal = max(array_sum($statusCounts), 1);
@endphp

    {{-- Welcome header --}}
    <div class="page-header">
        <div class="page-header-main">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ now()->format('l, d F Y') }}</p>
                <h1 class="page-title mt-1">{{ $greeting }}, {{ $firstName }} 👋</h1>
                <p class="page-subtitle">Here's what's happening in your store today.</p>
            </div>
        </div>
        <div class="page-actions">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-outline"><i class="ph ph-storefront"></i> Storefront</a>
            @if($u->hasPermission('manage_orders'))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline"><i class="ph ph-shopping-cart-simple"></i> Orders</a>
            @endif
            @if($u->hasPermission('manage_products'))
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add product</a>
            @endif
        </div>
    </div>

    {{-- Alert strip --}}
    @if($pendingOrders > 0 || $lowStockProducts->count() > 0)
        <div class="mb-5 flex flex-wrap gap-2.5">
            @if($pendingOrders > 0 && $u->hasPermission('manage_orders'))
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-[12.5px] font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/30">
                    <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span></span>
                    {{ __($pendingOrders == 1 ? ':count order awaiting confirmation' : ':count orders awaiting confirmation', ['count' => $pendingOrders]) }} <i class="ph ph-arrow-right"></i>
                </a>
            @endif
            @if($expressOpen > 0 && $u->hasPermission('manage_orders'))
                <a href="{{ route('admin.orders.index', ['delivery_type' => 'two_hours']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-[12.5px] font-bold bg-violet-50 text-violet-800 border border-violet-200 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:border-violet-500/30">
                    <i class="ph-fill ph-lightning"></i> {{ __($expressOpen == 1 ? ':count express (2-hour) delivery in progress' : ':count express (2-hour) deliveries in progress', ['count' => $expressOpen]) }}
                </a>
            @endif
            @if($lowStockProducts->count() > 0 && $u->hasPermission('manage_stock'))
                <a href="{{ route('admin.stock.index', ['filter' => 'low']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-[12.5px] font-bold bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/30">
                    <i class="ph-fill ph-warning"></i> {{ __($lowStockProducts->count() == 1 ? ':count product running low' : ':count products running low', ['count' => $lowStockProducts->count()]) }}
                </a>
            @endif
        </div>
    @endif

    {{-- KPIs --}}
    <div class="stat-grid cols-4">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label" title="Orders that are paid or delivered">Collected revenue</span>
                <span class="stat-icon tone-emerald"><i class="ph-duotone ph-currency-inr"></i></span>
            </div>
            <div class="stat-value">₹{{ number_format($totalRevenue, 0) }}</div>
            <div class="stat-meta">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">+₹{{ number_format($todayRevenue, 0) }}</span> today
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Last 7 days</span>
                <span class="stat-icon tone-blue"><i class="ph-duotone ph-chart-line-up"></i></span>
            </div>
            <div class="stat-value">₹{{ number_format($weekRevenue, 0) }}</div>
            <div class="stat-meta">
                @if(!is_null($revenueChange))
                    <span class="inline-flex items-center gap-0.5 font-bold {{ $revenueChange >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        <i class="ph-bold {{ $revenueChange >= 0 ? 'ph-trend-up' : 'ph-trend-down' }}"></i>{{ abs($revenueChange) }}%
                    </span> {{ __('vs previous week') }}
                @else
                    {{ __('Avg. order :amount', ['amount' => '₹'.number_format($avgOrderValue, 0)]) }}
                @endif
            </div>
        </div>
        <a href="{{ $u->hasPermission('manage_orders') ? route('admin.orders.index') : '#' }}" class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Orders</span>
                <span class="stat-icon tone-amber"><i class="ph-duotone ph-shopping-cart-simple"></i></span>
            </div>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
            <div class="stat-meta"><b class="text-slate-700 dark:text-slate-200">{{ $todayOrders }}</b> today · <b class="text-amber-600 dark:text-amber-400">{{ $pendingOrders }}</b> pending</div>
        </a>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Customers</span>
                <span class="stat-icon tone-violet"><i class="ph-duotone ph-users-three"></i></span>
            </div>
            <div class="stat-value">{{ number_format($totalCustomers) }}</div>
            <div class="stat-meta"><b class="text-violet-600 dark:text-violet-400">+{{ $newCustomersWeek }}</b> new this week</div>
        </div>
        <a href="{{ $u->hasPermission('manage_products') ? route('admin.products.index') : '#' }}" class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Products</span>
                <span class="stat-icon tone-slate"><i class="ph-duotone ph-package"></i></span>
            </div>
            <div class="stat-value">{{ number_format($totalProducts) }}</div>
            <div class="stat-meta">{{ __('Avg. order value :amount', ['amount' => '₹'.number_format($avgOrderValue, 0)]) }}</div>
        </a>
        <a href="{{ $u->hasPermission('manage_stock') ? route('admin.stock.index', ['filter' => 'low']) : '#' }}" class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Low stock</span>
                <span class="stat-icon tone-orange"><i class="ph-duotone ph-warning"></i></span>
            </div>
            <div class="stat-value">{{ $lowStockProducts->count() }}</div>
            <div class="stat-meta">At or below reorder level</div>
        </a>
        <a href="{{ $u->hasPermission('manage_stock') ? route('admin.stock.index', ['filter' => 'out']) : '#' }}" class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Out of stock</span>
                <span class="stat-icon tone-rose"><i class="ph-duotone ph-prohibit"></i></span>
            </div>
            <div class="stat-value">{{ $outOfStockCount }}</div>
            <div class="stat-meta">Hidden from checkout</div>
        </a>
        <a href="{{ $u->hasPermission('manage_offers') ? route('admin.offers.index') : '#' }}" class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Active offers</span>
                <span class="stat-icon tone-cyan"><i class="ph-duotone ph-ticket"></i></span>
            </div>
            <div class="stat-value">{{ $activeOffers }}</div>
            <div class="stat-meta">Coupons customers can use</div>
        </a>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
        <div class="card xl:col-span-2">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-chart-line-up"></i> Sales trend</h3>
                    <p class="card-subtitle">Revenue from non-cancelled orders, last 7 days</p>
                </div>
                <span class="badge badge-neutral">{{ __(':amount this week', ['amount' => '₹'.number_format($weekRevenue, 0)]) }}</span>
            </div>
            <div class="card-body">
                <div class="relative h-72"><canvas id="salesChart" aria-label="Sales trend chart" role="img"></canvas></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-chart-donut"></i> Order pipeline</h3>
                    <p class="card-subtitle">All orders by current status</p>
                </div>
            </div>
            <div class="card-body">
                <div class="relative h-44 mb-4">
                    <canvas id="statusChart" aria-label="Order status chart" role="img"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ array_sum($statusCounts) }}</span>
                        <span class="text-[11px] font-semibold text-slate-500">orders</span>
                    </div>
                </div>
                <ul class="space-y-2">
                    @foreach($statusMeta as $key => [$label, $color])
                        <li>
                            <a href="{{ $u->hasPermission('manage_orders') ? route('admin.orders.index', ['status' => $key]) : '#' }}" class="flex items-center gap-2.5 text-[12.5px] group">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $color }}"></span>
                                <span class="font-semibold text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white">{{ $label }}</span>
                                <span class="flex-1 mx-2 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden"><span class="block h-full rounded-full" style="width: {{ round($statusCounts[$key] / $statusTotal * 100) }}%; background: {{ $color }}"></span></span>
                                <span class="font-bold text-slate-900 dark:text-white tabular-nums w-6 text-right">{{ $statusCounts[$key] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Recent orders + side column --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
        <div class="card xl:col-span-2 overflow-hidden">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-receipt"></i> Recent orders</h3>
                    <p class="card-subtitle">{{ __('The latest :count orders placed', ['count' => $recentOrders->count()]) }}</p>
                </div>
                @if($u->hasPermission('manage_orders'))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">View all <i class="ph ph-arrow-right"></i></a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th class="hidden md:table-cell">Delivery</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            @php $sm = $statusMeta[$order->order_status] ?? [ucfirst($order->order_status), '#94a3b8', 'badge-neutral']; @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-slate-900 dark:text-white hover:underline">{{ $order->order_number }}</a>
                                    <span class="block text-[11px] text-slate-400">{{ $order->created_at->format('d M, h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $order->customer_name }}</span>
                                    <span class="block text-[11px] text-slate-400">{{ $order->customer_phone }}</span>
                                </td>
                                <td class="hidden md:table-cell">
                                    @if($order->delivery_type === 'two_hours')
                                        <span class="badge badge-violet"><i class="ph-fill ph-lightning"></i> 2-hour</span>
                                    @else
                                        <span class="badge badge-info"><i class="ph ph-calendar-blank"></i> Next day</span>
                                    @endif
                                </td>
                                <td class="font-bold text-slate-900 dark:text-white whitespace-nowrap">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td><span class="badge {{ $sm[2] }} badge-dot">{{ $sm[0] }}</span></td>
                                <td class="text-right">
                                    <div class="act justify-end">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="act-btn" title="View order"><i class="ph ph-eye"></i></a>
                                        <a href="{{ route('admin.invoices.show', $order) }}" class="act-btn hidden sm:inline-grid" title="Invoice"><i class="ph ph-receipt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><i class="ph-duotone ph-shopping-cart-simple"></i><h4>No orders yet</h4><p>New orders will show up here.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ph-duotone ph-warning"></i> Low stock alerts</h3>
                    @if($u->hasPermission('manage_stock'))
                        <a href="{{ route('admin.stock.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white">Stock sheet <i class="ph ph-arrow-right"></i></a>
                    @endif
                </div>
                <div class="p-2 max-h-[22rem] overflow-y-auto">
                    @forelse($lowStockProducts->take(8) as $product)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <img src="{{ $product->thumbnail_url }}" class="thumb" alt="" loading="lazy">
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-bold text-slate-900 dark:text-white truncate">{{ $product->name_en }}</p>
                                <p class="text-[11px] text-slate-500">{{ $product->unit }} · ₹{{ number_format($product->effective_price, 2) }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="badge {{ $product->stock_quantity <= 0 ? 'badge-danger' : 'badge-warning' }}">{{ $product->stock_quantity <= 0 ? 'Out' : $product->stock_quantity.' left' }}</span>
                                @if($u->hasPermission('manage_stock'))
                                    <a href="{{ route('admin.stock.index', ['filter' => $product->stock_quantity <= 0 ? 'out' : 'low']) }}" class="block mt-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Restock</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-8"><i class="ph-duotone ph-check-circle text-emerald-400"></i><h4>Inventory looks healthy</h4><p>No products are below their reorder level.</p></div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ph-duotone ph-trophy"></i> Best sellers</h3>
                </div>
                <div class="p-2">
                    @forelse($topProducts as $i => $tp)
                        <div class="flex items-center gap-3 p-2.5">
                            <span class="w-7 h-7 rounded-lg grid place-items-center text-xs font-extrabold {{ $i === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $i + 1 }}</span>
                            <span class="flex-1 min-w-0 text-[13px] font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $tp->name }}</span>
                            <span class="text-right">
                                <span class="block text-[12.5px] font-bold text-slate-900 dark:text-white">{{ __(':count sold', ['count' => (int) $tp->qty]) }}</span>
                                <span class="block text-[11px] text-slate-500">₹{{ number_format($tp->revenue, 0) }}</span>
                            </span>
                        </div>
                    @empty
                        <div class="empty-state py-8"><i class="ph-duotone ph-trophy"></i><h4>No sales yet</h4></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="card mt-5">
        <div class="card-header"><h3 class="card-title"><i class="ph-duotone ph-lightning"></i> Quick actions</h3></div>
        <div class="card-body grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $qa = array_filter([
                    $u->hasPermission('manage_products') ? ['Add product', 'package', route('admin.products.create'), 'tone-emerald'] : null,
                    $u->hasPermission('manage_stock') ? ['Update stock', 'warehouse', route('admin.stock.index'), 'tone-orange'] : null,
                    $u->hasPermission('manage_orders') ? ['Process orders', 'truck', route('admin.orders.index', ['status' => 'pending']), 'tone-amber'] : null,
                    $u->hasPermission('manage_offers') ? ['New coupon', 'ticket', route('admin.offers.create'), 'tone-cyan'] : null,
                    $u->hasPermission('manage_sliders') ? ['Home banner', 'slideshow', route('admin.sliders.create'), 'tone-violet'] : null,
                    $u->hasPermission('manage_settings') ? ['Theme settings', 'palette', route('admin.settings.index'), 'tone-blue'] : null,
                ]);
            @endphp
            @foreach($qa as [$label, $icon, $url, $tone])
                <a href="{{ $url }}" class="group flex flex-col items-center gap-2.5 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-lift transition text-center">
                    <span class="stat-icon {{ $tone }} group-hover:scale-110 transition"><i class="ph-duotone ph-{{ $icon }}"></i></span>
                    <span class="text-[12.5px] font-bold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx && window.Chart) {
            const ctx = salesCtx.getContext('2d');
            const grad = ctx.createLinearGradient(0, 0, 0, 280);
            grad.addColorStop(0, 'rgba(16, 185, 129, .28)');
            grad.addColorStop(1, 'rgba(16, 185, 129, 0)');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($salesTrend['labels']),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($salesTrend['data']),
                        borderColor: '#10b981',
                        backgroundColor: grad,
                        fill: true,
                        tension: .4,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#10b981',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: c => ' ₹' + Number(c.parsed.y).toLocaleString('en-IN', { maximumFractionDigits: 2 }) } }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: false } },
                        y: { beginAtZero: true, border: { display: false }, ticks: { callback: v => '₹' + Number(v).toLocaleString('en-IN'), maxTicksLimit: 6 } }
                    }
                }
            });
        }

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && window.Chart) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json(array_column($statusMeta, 0)),
                    datasets: [{
                        data: @json(array_values($statusCounts)),
                        backgroundColor: @json(array_column($statusMeta, 1)),
                        borderWidth: 0,
                        hoverOffset: 6,
                        spacing: 2,
                        borderRadius: 4,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '74%', plugins: { legend: { display: false } } }
            });
        }
    })();
</script>
@endpush
