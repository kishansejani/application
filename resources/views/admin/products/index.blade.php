@extends('admin.layouts.admin')

@section('title', 'Manage Products')
@section('page-title', 'Product Catalog')
@section('page-subtitle', 'Manage inventory, prices, multi-image gallery, and bilingual product content')

@section('action-buttons')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.stock.index') }}" class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-warehouse"></i>
        <span>Stock Overview</span>
    </a>
    <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        <span>Add New Product</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <!-- Filter bar -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="category_id" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="stock_status" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock (> 5)</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (≤ 5)</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>
            @if(request('category_id') || request('stock_status'))
                <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Clear Filters</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table id="productsTable" class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <th class="p-3">Product</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Unit & Price</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Featured</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shrink-0 shadow-sm">
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name_en }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-slate-900 text-sm hover:text-brand-600">
                                        {{ $product->name_en }}
                                    </a>
                                    <div class="text-xs text-brand-700 font-semibold">{{ $product->name_gu }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]">
                                {{ $product->category->name_en ?? 'N/A' }}
                            </span>
                            @if($product->subCategory)
                                <div class="text-[10px] text-slate-400 mt-0.5">&bull; {{ $product->subCategory->name_en }}</div>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="text-[11px] text-slate-500 font-semibold">{{ $product->unit }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="font-bold text-slate-900 text-sm">₹{{ number_format($product->effective_price, 2) }}</span>
                                @if($product->has_discount)
                                    <span class="text-[11px] text-slate-400 line-through">₹{{ number_format($product->price, 2) }}</span>
                                    <span class="text-[10px] font-bold text-emerald-600">({{ $product->discount_percent }}% OFF)</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3">
                            @if($product->stock_quantity <= 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                    Out of Stock (0)
                                </span>
                            @elseif($product->is_low_stock)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Low Stock ({{ $product->stock_quantity }})
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $product->stock_quantity }} in stock
                                </span>
                            @endif
                        </td>
                        <td class="p-3">
                            <button onclick="toggleProductFeatured({{ $product->id }}, this)" class="p-1.5 rounded-lg transition-colors {{ $product->is_featured ? 'text-amber-500 bg-amber-50' : 'text-slate-300 hover:text-amber-400' }}" title="Toggle Featured">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </td>
                        <td class="p-3">
                            <button onclick="toggleProductStatus({{ $product->id }}, this)" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all {{ $product->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Store Page">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="confirm-delete-btn p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#productsTable').DataTable({
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

    function toggleProductFeatured(id, btn) {
        $.ajax({
            url: `/admin/products/${id}/toggle-featured`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_featured) {
                        $(btn).removeClass('text-slate-300').addClass('text-amber-500 bg-amber-50');
                        toastr.success('Marked as featured product');
                    } else {
                        $(btn).removeClass('text-amber-500 bg-amber-50').addClass('text-slate-300');
                        toastr.info('Removed from featured products');
                    }
                }
            }
        });
    }

    function toggleProductStatus(id, btn) {
        $.ajax({
            url: `/admin/products/${id}/toggle-status`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_active) {
                        $(btn).removeClass('bg-slate-100 text-slate-500').addClass('bg-emerald-100 text-emerald-800').text('Active');
                        toastr.success('Product published');
                    } else {
                        $(btn).removeClass('bg-emerald-100 text-emerald-800').addClass('bg-slate-100 text-slate-500').text('Inactive');
                        toastr.info('Product hidden');
                    }
                }
            }
        });
    }
</script>
@endpush
