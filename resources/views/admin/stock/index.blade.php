@extends('admin.layouts.admin')

@section('title')
Stock & Inventory
@endsection

@section('content')
    <x-admin.page-header title="Stock & inventory" subtitle="Live inventory levels, quick adjustments and low-stock alerts." icon="warehouse">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline"><i class="ph ph-package"></i> Products</a>
        <button type="button" onclick="openBulkAdjustModal()" class="btn btn-primary"><i class="ph ph-sliders-horizontal"></i> Bulk adjustment</button>
    </x-admin.page-header>

    @php
        $filter = request('filter');
        $catParam = request('category_id') ? ['category_id' => request('category_id')] : [];
    @endphp
    <div class="stat-grid cols-4">
        <x-admin.stat-card label="Units in stock" :value="number_format($stats['total_items'])" icon="stack" tone="slate" meta="Across all products" />
        <x-admin.stat-card label="Tracked products" :value="$stats['total_products']" icon="package" tone="emerald"
            :href="route('admin.stock.index')" :active="!$filter && !request('category_id')" meta="View all stock" />
        <x-admin.stat-card label="Low stock" :value="$stats['low_stock_count']" icon="warning" tone="amber"
            :href="route('admin.stock.index', ['filter' => 'low'])" :active="$filter === 'low'" meta="At or below alert level" />
        <x-admin.stat-card label="Out of stock" :value="$stats['out_of_stock_count']" icon="prohibit" tone="rose"
            :href="route('admin.stock.index', ['filter' => 'out'])" :active="$filter === 'out'" meta="Needs restocking" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.stock.index', $catParam) }}" class="filter-chip {{ !$filter ? 'is-active' : '' }}">All</a>
                <a href="{{ route('admin.stock.index', ['filter' => 'healthy'] + $catParam) }}" class="filter-chip {{ $filter === 'healthy' ? 'is-active' : '' }}"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Healthy</a>
                <a href="{{ route('admin.stock.index', ['filter' => 'low'] + $catParam) }}" class="filter-chip {{ $filter === 'low' ? 'is-active' : '' }}"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low ({{ $stats['low_stock_count'] }})</a>
                <a href="{{ route('admin.stock.index', ['filter' => 'out'] + $catParam) }}" class="filter-chip {{ $filter === 'out' ? 'is-active' : '' }}"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out ({{ $stats['out_of_stock_count'] }})</a>
            </div>
            <form action="{{ route('admin.stock.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 sm:ml-auto" data-no-loading>
                @if($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <select name="category_id" onchange="this.form.submit()" class="form-select w-auto min-w-[11rem]" aria-label="Filter by category" data-search>
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }}</option>
                    @endforeach
                </select>
                @if($filter || request('category_id'))
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-ghost btn-sm text-rose-600"><i class="ph ph-x-circle"></i> Clear</a>
                @endif
            </form>
        </div>

        <table id="stockTable" class="w-full" data-export-title="Stock & inventory">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="export-only">SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Alert at</th>
                    <th>Status</th>
                    <th class="text-right">Stock quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $prod)
                    <tr id="row-prod-{{ $prod->id }}">
                        <td data-export="{{ $prod->name_en }}{{ $prod->name_gu ? ' / '.$prod->name_gu : '' }} ({{ $prod->unit }})">
                            <div class="flex items-center gap-3 sm:min-w-[13rem]">
                                <img src="{{ $prod->thumbnail_url }}" alt="" class="thumb hidden sm:block" loading="lazy">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.products.edit', $prod) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $prod->name_en }}</a>
                                    @if($prod->name_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" lang="gu">{{ $prod->name_gu }}</span>@endif
                                    <span class="block text-[11px] text-slate-400 mt-0.5">{{ $prod->unit }} · <span class="font-mono">{{ $prod->sku }}</span></span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $prod->sku }}</td>
                        <td data-export="{{ $prod->category->name_en ?? 'N/A' }}">
                            <span class="badge badge-neutral">{{ $prod->category->name_en ?? 'N/A' }}</span>
                        </td>
                        <td class="whitespace-nowrap" data-order="{{ $prod->effective_price }}" data-export="₹{{ number_format($prod->effective_price, 2) }}">
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($prod->effective_price, 2) }}</span>
                        </td>
                        <td class="whitespace-nowrap" data-order="{{ $prod->low_stock_threshold }}" data-export="{{ $prod->low_stock_threshold }}">
                            <span class="text-slate-500 dark:text-slate-400">≤ {{ $prod->low_stock_threshold }} units</span>
                        </td>
                        <td id="stock-badge-{{ $prod->id }}" data-order="{{ $prod->stock_quantity <= 0 ? 0 : ($prod->is_low_stock ? 1 : 2) }}"
                            data-export="{{ $prod->stock_quantity <= 0 ? 'Out of stock' : ($prod->is_low_stock ? 'Low stock' : 'Healthy') }}">
                            @if($prod->stock_quantity <= 0)
                                <span class="badge badge-danger badge-dot">Out of stock</span>
                            @elseif($prod->is_low_stock)
                                <span class="badge badge-warning badge-dot">Low stock</span>
                            @else
                                <span class="badge badge-success badge-dot">Healthy</span>
                            @endif
                        </td>
                        <td class="text-right" data-order="{{ $prod->stock_quantity }}" id="stock-cell-{{ $prod->id }}">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <div class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden">
                                    <button type="button" onclick="quickAdjust({{ $prod->id }}, -10)" class="hidden lg:flex h-9 px-2 items-center text-[11px] font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border-r border-slate-200 dark:border-slate-700" title="Remove 10">−10</button>
                                    <button type="button" onclick="quickAdjust({{ $prod->id }}, -1)" class="h-9 w-8 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" title="Remove 1" aria-label="Remove 1"><i class="ph-bold ph-minus"></i></button>
                                    <input type="number" min="0" id="input-qty-{{ $prod->id }}" value="{{ $prod->stock_quantity }}" data-saved="{{ $prod->stock_quantity }}" aria-label="Stock quantity for {{ $prod->name_en }}"
                                        class="stock-input !w-12 sm:!w-16 !h-9 !rounded-none !border-0 !border-x !border-slate-200 dark:!border-slate-700 !bg-transparent text-center font-extrabold text-sm text-slate-900 dark:text-white !px-1 focus:!shadow-none"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault();saveStock({{ $prod->id }});}">
                                    <button type="button" onclick="quickAdjust({{ $prod->id }}, 1)" class="h-9 w-8 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" title="Add 1" aria-label="Add 1"><i class="ph-bold ph-plus"></i></button>
                                    <button type="button" onclick="quickAdjust({{ $prod->id }}, 10)" class="hidden lg:flex h-9 px-2 items-center text-[11px] font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border-l border-slate-200 dark:border-slate-700" title="Add 10">+10</button>
                                </div>
                                <button type="button" id="save-btn-{{ $prod->id }}" onclick="saveStock({{ $prod->id }})" class="act-btn" title="Save stock" aria-label="Save stock"><i class="ph ph-floppy-disk"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Bulk adjustment modal --}}
    <div id="bulkAdjustModal" class="fixed inset-0 z-[80] hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="bulkTitle">
        <div class="card w-full sm:max-w-2xl max-h-[92vh] flex flex-col !rounded-b-none sm:!rounded-b-[var(--radius-lg)] shadow-2xl">
            <div class="card-header">
                <div>
                    <h3 class="card-title" id="bulkTitle"><i class="ph-duotone ph-sliders-horizontal"></i> Bulk stock adjustment</h3>
                    <p class="card-subtitle">Rows left at 0 with “Add” are not changed.</p>
                </div>
                <button type="button" onclick="closeBulkAdjustModal()" class="act-btn" aria-label="Close"><i class="ph ph-x"></i></button>
            </div>

            <form action="{{ route('admin.stock.bulk_adjust') }}" method="POST" class="flex flex-col min-h-0 flex-1">
                @csrf
                <div class="px-5 pt-4">
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="search" id="bulkSearch" placeholder="Find a product…" class="form-control !pl-9" autocomplete="off" data-no-export>
                    </div>
                </div>
                <div class="overflow-y-auto px-5 py-4 space-y-2 min-h-0 flex-1" id="bulkList">
                    @foreach($products as $idx => $p)
                        <div class="bulk-row flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40" data-name="{{ strtolower($p->name_en.' '.$p->sku) }}">
                            <input type="hidden" name="adjustments[{{ $idx }}][product_id]" value="{{ $p->id }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $p->thumbnail_url }}" alt="" class="thumb !w-9 !h-9 !rounded-lg" loading="lazy">
                                <div class="min-w-0">
                                    <p class="text-[13px] font-bold text-slate-800 dark:text-slate-100 truncate">{{ $p->name_en }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $p->stock_quantity }} in stock · {{ $p->unit }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-auto">
                                <select name="adjustments[{{ $idx }}][type]" class="form-select !h-9 !text-xs w-auto" aria-label="Adjustment type">
                                    <option value="add">Add</option>
                                    <option value="subtract">Subtract</option>
                                    <option value="set">Set exact</option>
                                </select>
                                <input type="number" name="adjustments[{{ $idx }}][quantity]" value="0" min="0" class="form-control !w-20 !h-9 text-center font-bold" aria-label="Quantity">
                            </div>
                        </div>
                    @endforeach
                    <div id="bulkEmpty" class="empty-state hidden"><i class="ph-duotone ph-magnifying-glass"></i><h4>No matching products</h4></div>
                </div>
                <div class="flex items-center justify-end gap-2.5 px-5 py-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="closeBulkAdjustModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ph-bold ph-check"></i> Apply adjustments</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let stockTable;
    $(function () {
        stockTable = $('#stockTable').DataTable({
            order: [[6, 'asc']],
            columnDefs: [
                { targets: 1, visible: false },            // SKU: export-only
                { targets: 6, responsivePriority: 1 },
                { targets: 0, responsivePriority: 2 },
                { targets: [3, 4], responsivePriority: 4 }
            ]
        });

        // Highlight unsaved stepper values
        $(document).on('input', '.stock-input', function () { markDirty(this.id.replace('input-qty-', '')); });

        // Bulk modal search
        $('#bulkSearch').on('input', function () {
            const q = this.value.trim().toLowerCase();
            let shown = 0;
            $('#bulkList .bulk-row').each(function () { const ok = !q || this.dataset.name.includes(q); $(this).toggle(ok); if (ok) shown++; });
            $('#bulkEmpty').toggleClass('hidden', shown > 0);
        });
        $('#bulkAdjustModal').on('click', function (e) { if (e.target === this) closeBulkAdjustModal(); });
        $(document).on('keydown', function (e) { if (e.key === 'Escape' && !$('#bulkAdjustModal').hasClass('hidden')) closeBulkAdjustModal(); });
    });

    function markDirty(id) {
        const $in = $(`#input-qty-${id}`);
        const dirty = String(parseInt($in.val(), 10)) !== String($in.data('saved'));
        $in.toggleClass('!bg-amber-50 dark:!bg-amber-500/10', dirty);
        $(`#save-btn-${id}`).toggleClass('is-edit !bg-emerald-600 !text-white !border-emerald-600', dirty);
    }

    function quickAdjust(id, delta) {
        const input = $(`#input-qty-${id}`);
        let cur = parseInt(input.val(), 10) || 0;
        cur = Math.max(0, cur + delta);
        input.val(cur);
        markDirty(id);
    }

    function saveStock(id) {
        const qty = $(`#input-qty-${id}`).val();
        const $btn = $(`#save-btn-${id}`).prop('disabled', true);
        $.ajax({ url: `/admin/stock/${id}`, type: 'PATCH', data: { stock_quantity: qty } })
            .done(function (res) {
                if (!res.success) return;
                const $in = $(`#input-qty-${id}`).val(res.new_stock).data('saved', res.new_stock);
                markDirty(id);
                let html, label, rank;
                if (res.is_out) { html = '<span class="badge badge-danger badge-dot">Out of stock</span>'; label = 'Out of stock'; rank = 0; }
                else if (res.is_low) { html = '<span class="badge badge-warning badge-dot">Low stock</span>'; label = 'Low stock'; rank = 1; }
                else { html = '<span class="badge badge-success badge-dot">Healthy</span>'; label = 'Healthy'; rank = 2; }
                $(`#stock-badge-${id}`).html(html).attr({ 'data-export': label, 'data-order': rank });
                $(`#stock-cell-${id}`).attr('data-order', res.new_stock);
                if (stockTable) stockTable.row($(`#row-prod-${id}`)).invalidate('dom');
                toastr.success(res.message);
            })
            .fail(function (xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update stock';
                toastr.error(msg);
            })
            .always(function () { $btn.prop('disabled', false); });
    }

    function openBulkAdjustModal() {
        $('#bulkAdjustModal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function () { $('#bulkSearch').trigger('focus'); }, 50);
    }

    function closeBulkAdjustModal() {
        $('#bulkAdjustModal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    }
</script>
@endpush
