@extends('admin.layouts.admin')

@section('title', 'Manage Stock & Inventory')
@section('page-title', 'Stock & Inventory Management')
@section('page-subtitle', 'Real-time stock adjustments, out-of-stock prevention, and low-stock alerts')

@section('action-buttons')
<button onclick="openBulkAdjustModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
    <i class="fa-solid fa-sliders"></i>
    <span>Bulk Stock Adjustment</span>
</button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Stat Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Total Inventory Units</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_items']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Low Stock Alerts</p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['low_stock_count'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Out of Stock</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['out_of_stock_count'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-ban"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Total Tracked Products</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total_products'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <!-- Filter buttons -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.stock.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !request('filter') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Items
                </a>
                <a href="{{ route('admin.stock.index', ['filter' => 'low']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request('filter') == 'low' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                    ⚠️ Low Stock Only ({{ $stats['low_stock_count'] }})
                </a>
                <a href="{{ route('admin.stock.index', ['filter' => 'out']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request('filter') == 'out' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                    🚫 Out of Stock ({{ $stats['out_of_stock_count'] }})
                </a>
            </div>

            <form action="{{ route('admin.stock.index') }}" method="GET" class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none">
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
            <table id="stockTable" class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <th class="p-3">Product Item</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Threshold</th>
                        <th class="p-3">Current Stock</th>
                        <th class="p-3">Stock Status</th>
                        <th class="p-3 text-right">Quick Stock Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($products as $prod)
                        <tr id="row-prod-{{ $prod->id }}" class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $prod->thumbnail_url }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $prod->name_en }}</div>
                                        <div class="text-xs text-brand-700 font-semibold">{{ $prod->name_gu }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $prod->unit }} • {{ $prod->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 font-semibold text-slate-600">
                                {{ $prod->category->name_en ?? 'N/A' }}
                            </td>
                            <td class="p-3 font-bold text-slate-900">
                                ₹{{ number_format($prod->effective_price, 2) }}
                            </td>
                            <td class="p-3 text-slate-500 font-semibold">
                                &le; {{ $prod->low_stock_threshold }} units
                            </td>
                            <td class="p-3 font-extrabold text-sm" id="stock-val-{{ $prod->id }}">
                                {{ $prod->stock_quantity }}
                            </td>
                            <td class="p-3" id="stock-badge-{{ $prod->id }}">
                                @if($prod->stock_quantity <= 0)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800">
                                        OUT OF STOCK
                                    </span>
                                @elseif($prod->is_low_stock)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                        LOW STOCK
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        HEALTHY
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="quickAdjust({{ $prod->id }}, -10)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs" title="-10">-10</button>
                                    <button onclick="quickAdjust({{ $prod->id }}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs" title="-1">-1</button>
                                    <input type="number" id="input-qty-{{ $prod->id }}" value="{{ $prod->stock_quantity }}" class="w-16 px-2 py-1 border border-slate-200 rounded-lg text-center font-bold text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                    <button onclick="quickAdjust({{ $prod->id }}, 1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs" title="+1">+1</button>
                                    <button onclick="quickAdjust({{ $prod->id }}, 10)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs" title="+10">+10</button>
                                    <button onclick="saveStock({{ $prod->id }})" class="p-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-colors" title="Save">
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
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <h3 class="text-lg font-bold text-slate-900">Bulk Stock Adjustment</h3>
            <button onclick="closeBulkAdjustModal()" class="p-2 text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="{{ route('admin.stock.bulk_adjust') }}" method="POST" class="space-y-4">
            @csrf
            <div class="max-h-96 overflow-y-auto space-y-3 pr-2">
                @foreach($products as $idx => $p)
                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="adjustments[{{ $idx }}][product_id]" value="{{ $p->id }}">
                            <span class="text-xs font-bold text-slate-800">{{ $p->name_en }}</span>
                            <span class="text-[10px] text-slate-400">({{ $p->stock_quantity }} current)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <select name="adjustments[{{ $idx }}][type]" class="px-2 py-1 text-xs border border-slate-200 rounded-lg bg-white">
                                <option value="add">+ Add</option>
                                <option value="subtract">- Subtract</option>
                                <option value="set">Set Exact</option>
                            </select>
                            <input type="number" name="adjustments[{{ $idx }}][quantity]" value="0" min="0" class="w-20 px-2 py-1 text-xs border border-slate-200 rounded-lg text-center font-bold">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeBulkAdjustModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/20">Apply Bulk Adjustments</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#stockTable').DataTable({
            responsive: true,
            pageLength: 15,
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', className: 'px-3 py-1.5 text-xs bg-slate-100 rounded-lg mr-2 font-semibold' },
                { extend: 'csv', className: 'px-3 py-1.5 text-xs bg-slate-100 rounded-lg mr-2 font-semibold' },
                { extend: 'print', className: 'px-3 py-1.5 text-xs bg-slate-100 rounded-lg font-semibold' }
            ]
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
