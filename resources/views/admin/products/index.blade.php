@extends('admin.layouts.admin')

@section('title', 'Products')

@section('content')
    <x-admin.page-header title="Products" subtitle="Manage catalog prices, units, stock levels and bilingual product details." icon="package">
        <a href="{{ route('admin.stock.index') }}" class="btn btn-outline"><i class="ph ph-warehouse"></i> Stock overview</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add product</a>
    </x-admin.page-header>

    @php $noFilter = !request('stock_status') && !request('category_id'); @endphp
    <div class="stat-grid cols-5">
        <x-admin.stat-card label="All products" :value="$stats['total'] ?? $products->count()" icon="package" tone="slate"
            :href="route('admin.products.index')" :active="$noFilter" meta="Total catalog" />
        <x-admin.stat-card label="In stock" :value="$stats['in_stock'] ?? 0" icon="check-circle" tone="emerald"
            :href="route('admin.products.index', ['stock_status' => 'in_stock'])" :active="request('stock_status') === 'in_stock'" meta="More than 5 units" />
        <x-admin.stat-card label="Low stock" :value="$stats['low_stock'] ?? 0" icon="warning" tone="amber"
            :href="route('admin.products.index', ['stock_status' => 'low_stock'])" :active="request('stock_status') === 'low_stock'" meta="1 – 5 units left" />
        <x-admin.stat-card label="Out of stock" :value="$stats['out_of_stock'] ?? 0" icon="prohibit" tone="rose"
            :href="route('admin.products.index', ['stock_status' => 'out_of_stock'])" :active="request('stock_status') === 'out_of_stock'" meta="Needs restocking" />
        <x-admin.stat-card label="Featured" :value="$stats['featured'] ?? 0" icon="star" tone="violet" meta="Shown on home page" class="col-span-2 md:col-span-1" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5" data-no-loading>
                <select name="category_id" onchange="this.form.submit()" class="form-select w-auto min-w-[11rem]" data-search aria-label="Filter by category">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }}</option>
                    @endforeach
                </select>
                <select name="stock_status" onchange="this.form.submit()" class="form-select w-auto min-w-[10rem]">
                    <option value="">All stock levels</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In stock (&gt; 5)</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low stock (≤ 5)</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                </select>
                @if(!$noFilter)
                    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm text-rose-600"><i class="ph ph-x-circle"></i> Clear filters</a>
                @endif
            </form>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $products->count() }} {{ \Illuminate\Support\Str::plural('product', $products->count()) }}</span>
        </div>

        <table id="productsTable" class="w-full" data-export-title="Products">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="export-only">SKU</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th class="text-center">Featured</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td data-export="{{ $product->name_en }}{{ $product->name_gu ? ' / '.$product->name_gu : '' }}">
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                <img src="{{ $product->thumbnail_url }}" alt="" class="thumb" loading="lazy">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $product->name_en }}</a>
                                    @if($product->name_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $product->name_gu }}</span>@endif
                                    <span class="block font-mono text-[10.5px] text-slate-400 mt-0.5">{{ $product->sku }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->sku }}</td>
                        <td data-export="{{ $product->category->name_en ?? 'N/A' }}{{ $product->subCategory ? ' › '.$product->subCategory->name_en : '' }}">
                            <span class="badge badge-neutral">{{ $product->category->name_en ?? 'N/A' }}</span>
                            @if($product->subCategory)
                                <span class="block text-[11px] text-slate-400 mt-1">{{ $product->subCategory->name_en }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">{{ $product->unit }}</td>
                        <td data-order="{{ $product->effective_price }}" data-export="₹{{ number_format($product->effective_price, 2) }}" class="whitespace-nowrap">
                            <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($product->effective_price, 2) }}</span>
                            @if($product->has_discount)
                                <span class="block text-[11px]"><span class="line-through text-slate-400">₹{{ number_format($product->price, 2) }}</span> <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $product->discount_percent }}% off</span></span>
                            @endif
                        </td>
                        <td data-order="{{ $product->stock_quantity }}" data-export="{{ $product->stock_quantity }}">
                            @if($product->stock_quantity <= 0)
                                <span class="badge badge-danger badge-dot">Out of stock</span>
                            @elseif($product->is_low_stock)
                                <span class="badge badge-warning badge-dot">Low · {{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge badge-success badge-dot">{{ $product->stock_quantity }} in stock</span>
                            @endif
                        </td>
                        <td class="text-center" data-order="{{ $product->is_featured ? 1 : 0 }}" data-export="{{ $product->is_featured ? 'Yes' : 'No' }}">
                            <button type="button" onclick="toggleProductFeatured({{ $product->id }}, this)" class="act-btn {{ $product->is_featured ? '!text-amber-500 !border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:!border-amber-500/30' : '' }}" title="Toggle featured" aria-pressed="{{ $product->is_featured ? 'true' : 'false' }}">
                                <i class="{{ $product->is_featured ? 'ph-fill' : 'ph' }} ph-star"></i>
                            </button>
                        </td>
                        <td data-order="{{ $product->is_active ? 1 : 0 }}" data-export="{{ $product->is_active ? 'Active' : 'Inactive' }}">
                            <button type="button" onclick="toggleProductStatus({{ $product->id }}, this)" class="switch {{ $product->is_active ? 'is-on' : '' }}" role="switch" aria-checked="{{ $product->is_active ? 'true' : 'false' }}">
                                <span class="switch-track"></span><span class="switch-text">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                            </button>
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="act-btn" title="View on storefront"><i class="ph ph-arrow-square-out"></i></a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete “{{ $product->name_en }}”?" title="Delete"><i class="ph ph-trash"></i></button>
                                </form>
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
        $('#productsTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 1, visible: false },            // SKU: export-only (toggle via “Columns”)
                { targets: [4, 5], responsivePriority: 3 }
            ]
        });
    });

    function toggleProductFeatured(id, btn) {
        $.ajax({ url: `/admin/products/${id}/toggle-featured`, type: 'PATCH' })
            .done(function (res) {
                if (!res.success) return;
                const on = !!res.is_featured;
                $(btn).toggleClass('!text-amber-500 !border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:!border-amber-500/30', on)
                      .attr('aria-pressed', on ? 'true' : 'false')
                      .find('i').attr('class', (on ? 'ph-fill' : 'ph') + ' ph-star');
                $(btn).closest('td').attr('data-export', on ? 'Yes' : 'No');
                toastr[on ? 'success' : 'info'](on ? 'Marked as featured product' : 'Removed from featured products');
            })
            .fail(function () { toastr.error('Could not update the product. Please try again.'); });
    }

    function toggleProductStatus(id, btn) {
        $.ajax({ url: `/admin/products/${id}/toggle-status`, type: 'PATCH' })
            .done(function (res) {
                if (!res.success) return;
                const on = !!res.is_active;
                $(btn).toggleClass('is-on', on).attr('aria-checked', on ? 'true' : 'false').find('.switch-text').text(on ? 'Active' : 'Hidden');
                $(btn).closest('td').attr('data-export', on ? 'Active' : 'Inactive');
                toastr[on ? 'success' : 'info'](on ? 'Product is now visible on the storefront' : 'Product hidden from the storefront');
            })
            .fail(function () { toastr.error('Could not update the product. Please try again.'); });
    }
</script>
@endpush
