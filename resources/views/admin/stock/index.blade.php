@extends('admin.layouts.admin')

@section('title', 'Stock')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Stock & Inventory</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Real-time inventory levels, quick adjustments, and low-stock alerts.</p>
        </div>
        <button onclick="openBulkAdjustModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
            <i class="fa-solid fa-sliders text-xs"></i>
            <span>Bulk Stock Adjustment</span>
        </button>
    </div>

    <!-- Stat Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Card 1: Total Units -->
        <div class="group relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-md border border-slate-700/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-300">Total Units</span>
                <div class="w-8 h-8 rounded-xl bg-white/10 text-indigo-300 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-white">{{ number_format($stats['total_items']) }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/10 text-slate-300">In Warehouse</span>
            </div>
            <i class="fa-solid fa-boxes-stacked absolute -right-3 -bottom-3 text-5xl opacity-5 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card 2: Tracked SKUs -->
        <div class="group relative overflow-hidden rounded-2xl p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-emerald-500 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Tracked SKUs</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['total_products'] }}</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                </span>
            </div>
            <i class="fa-solid fa-circle-check absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card 3: Low Stock Alerts -->
        <div class="group relative overflow-hidden rounded-2xl p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-amber-500 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Low Stock Alerts</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['low_stock_count'] }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300">Reorder</span>
            </div>
            <i class="fa-solid fa-triangle-exclamation absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card 4: Out of Stock -->
        <div class="group relative overflow-hidden rounded-2xl p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-rose-500 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-400">Out of Stock</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['out_of_stock_count'] }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300">Critical</span>
            </div>
            <i class="fa-solid fa-ban absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    <!-- Stock Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <!-- Filter buttons -->
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.stock.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request('filter') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All Items
                </a>
                <a href="{{ route('admin.stock.index', ['filter' => 'low']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') == 'low' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 hover:bg-amber-100' }}">
                    ⚠️ Low Stock ({{ $stats['low_stock_count'] }})
                </a>
                <a href="{{ route('admin.stock.index', ['filter' => 'out']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') == 'out' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 hover:bg-rose-100' }}">
                    🚫 Out of Stock ({{ $stats['out_of_stock_count'] }})
                </a>
            </div>

            <form action="{{ route('admin.stock.index') }}" method="GET" class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none">
                    <option value="">Filter by Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_en }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table id="stockTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Product Item</th>
                        <th class="px-4 py-3.5">Category</th>
                        <th class="px-4 py-3.5">Price</th>
                        <th class="px-4 py-3.5">Threshold</th>
                        <th class="px-4 py-3.5">Stock Qty</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Quick Stock Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($products as $prod)
                        <tr id="row-prod-{{ $prod->id }}" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $prod->thumbnail_url }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $prod->name_en }}</div>
                                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $prod->name_gu }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $prod->unit }} • {{ $prod->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-slate-600 dark:text-slate-300">
                                {{ $prod->category->name_en ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                                ₹{{ number_format($prod->effective_price, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 font-semibold">
                                &le; {{ $prod->low_stock_threshold }} units
                            </td>
                            <td class="px-4 py-3.5 font-black text-sm" id="stock-val-{{ $prod->id }}">
                                {{ $prod->stock_quantity }}
                            </td>
                            <td class="px-4 py-3.5" id="stock-badge-{{ $prod->id }}">
                                @if($prod->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OUT OF STOCK
                                    </span>
                                @elseif($prod->is_low_stock)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> LOW STOCK
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> HEALTHY
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="quickAdjust({{ $prod->id }}, -10)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs" title="-10">-10</button>
                                    <button onclick="quickAdjust({{ $prod->id }}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs" title="-1">-1</button>
                                    <input type="number" id="input-qty-{{ $prod->id }}" value="{{ $prod->stock_quantity }}" class="w-16 px-2 py-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-center font-bold text-xs text-slate-900 dark:text-white focus:outline-none">
                                    <button onclick="quickAdjust({{ $prod->id }}, 1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs" title="+1">+1</button>
                                    <button onclick="quickAdjust({{ $prod->id }}, 10)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs" title="+10">+10</button>
                                    <button onclick="saveStock({{ $prod->id }})" class="w-8 h-8 rounded-xl bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 flex items-center justify-center text-xs font-bold transition shadow-sm" title="Save Stock">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bulk Adjustment Modal -->
<div id="bulkAdjustModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Bulk Stock Adjustment</h3>
            <button onclick="closeBulkAdjustModal()" class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="{{ route('admin.stock.bulk_adjust') }}" method="POST" class="space-y-4">
            @csrf
            <div class="max-h-96 overflow-y-auto space-y-3 pr-2">
                @foreach($products as $idx => $p)
                    <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="adjustments[{{ $idx }}][product_id]" value="{{ $p->id }}">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $p->name_en }}</span>
                            <span class="text-[10px] text-slate-400">({{ $p->stock_quantity }} current)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <select name="adjustments[{{ $idx }}][type]" class="px-2.5 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                <option value="add">+ Add</option>
                                <option value="subtract">- Subtract</option>
                                <option value="set">Set Exact</option>
                            </select>
                            <input type="number" name="adjustments[{{ $idx }}][quantity]" value="0" min="0" class="w-20 px-2 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded-xl text-center font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeBulkAdjustModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 text-xs font-bold shadow-md">Apply Bulk Adjustments</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#stockTable').DataTable({
            order: [[4, 'asc']]
        });
    });

    function quickAdjust(id, delta) {
        const input = $(`#input-qty-${id}`);
        let cur = parseInt(input.val()) || 0;
        cur = Math.max(0, cur + delta);
        input.val(cur);
    }

    function saveStock(id) {
        const qty = $(`#input-qty-${id}`).val();
        $.ajax({
            url: `/admin/stock/${id}`,
            type: 'PATCH',
            data: { stock_quantity: qty },
            success: function(res) {
                if (res.success) {
                    $(`#stock-val-${id}`).text(res.new_stock);
                    
                    let badgeHtml = '';
                    if (res.is_out) {
                        badgeHtml = '<span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800">OUT OF STOCK</span>';
                    } else if (res.is_low) {
                        badgeHtml = '<span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">LOW STOCK</span>';
                    } else {
                        badgeHtml = '<span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">HEALTHY</span>';
                    }
                    $(`#stock-badge-${id}`).html(badgeHtml);
                    toastr.success(res.message);
                }
            },
            error: function() {
                toastr.error('Failed to update stock');
            }
        });
    }

    function openBulkAdjustModal() {
        $('#bulkAdjustModal').removeClass('hidden');
    }

    function closeBulkAdjustModal() {
        $('#bulkAdjustModal').addClass('hidden');
    }
</script>
@endpush
