@extends('admin.layouts.admin')

@section('title', 'Products')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Products & Inventory</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage catalog prices, units, stock quantities, and bilingual details.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.stock.index') }}" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-2xl text-xs font-bold shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-xs text-slate-400"></i>
                <span>Stock Overview</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add New Product</span>
            </a>
        </div>
    </div>

    <!-- Top KPI / Pipeline Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('admin.products.index') }}" class="p-4 rounded-2xl border transition-all {{ !request('stock_status') && !request('category_id') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-slate-900 dark:border-white shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">All Products</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['total'] ?? $products->count() }}</h4>
        </a>

        <a href="{{ route('admin.products.index', ['stock_status' => 'in_stock']) }}" class="p-4 rounded-2xl border transition-all {{ request('stock_status') == 'in_stock' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">In Stock (>5)</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['in_stock'] ?? 0 }}</h4>
        </a>

        <a href="{{ route('admin.products.index', ['stock_status' => 'low_stock']) }}" class="p-4 rounded-2xl border transition-all {{ request('stock_status') == 'low_stock' ? 'bg-amber-500 text-white border-amber-500 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-amber-50 dark:hover:bg-amber-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">⚠️ Low Stock</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['low_stock'] ?? 0 }}</h4>
        </a>

        <a href="{{ route('admin.products.index', ['stock_status' => 'out_of_stock']) }}" class="p-4 rounded-2xl border transition-all {{ request('stock_status') == 'out_of_stock' ? 'bg-rose-600 text-white border-rose-600 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-rose-50 dark:hover:bg-rose-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">🚫 Out of Stock</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['out_of_stock'] ?? 0 }}</h4>
        </a>

        <div class="p-4 rounded-2xl border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75 text-amber-500">⭐ Featured</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['featured'] ?? 0 }}</h4>
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <!-- Filter bar -->
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700/60">
            <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div>
                    <select name="category_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-slate-400 focus:outline-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="stock_status" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-slate-400 focus:outline-none">
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock (> 5)</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (≤ 5)</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>
                </div>
                @if(request('category_id') || request('stock_status'))
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-xmark"></i> Clear Filters
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table id="productsTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Product</th>
                        <th class="px-4 py-3.5">Category</th>
                        <th class="px-4 py-3.5">Unit & Price</th>
                        <th class="px-4 py-3.5">Stock</th>
                        <th class="px-4 py-3.5">Featured</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 shrink-0 shadow-sm">
                                        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name_en }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-slate-900 dark:text-white text-sm hover:underline leading-tight">
                                            {{ $product->name_en }}
                                        </a>
                                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $product->name_gu }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-block px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px]">
                                    {{ $product->category->name_en ?? 'N/A' }}
                                </span>
                                @if($product->subCategory)
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">&bull; {{ $product->subCategory->name_en }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">{{ $product->unit }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="font-bold text-slate-900 dark:text-white text-sm">₹{{ number_format($product->effective_price, 2) }}</span>
                                    @if($product->has_discount)
                                        <span class="text-[11px] text-slate-400 line-through">₹{{ number_format($product->price, 2) }}</span>
                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">({{ $product->discount_percent }}% OFF)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
                                    </span>
                                @elseif($product->is_low_stock)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low ({{ $product->stock_quantity }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $product->stock_quantity }} in stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <button onclick="toggleProductFeatured({{ $product->id }}, this)" class="p-1.5 rounded-xl transition {{ $product->is_featured ? 'text-amber-500 bg-amber-50 dark:bg-amber-950/50' : 'text-slate-300 dark:text-slate-600 hover:text-amber-400' }}" title="Toggle Featured">
                                    <i class="fa-solid fa-star text-sm"></i>
                                </button>
                            </td>
                            <td class="px-4 py-3.5">
                                <button onclick="toggleProductStatus({{ $product->id }}, this)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm border {{ $product->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $product->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span>{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 flex items-center justify-center transition shadow-sm" title="View Storefront">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Edit Product">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="confirm-delete-btn w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Delete Product">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
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
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#productsTable').DataTable({
            order: [[0, 'asc']]
        });
    });

    function toggleProductFeatured(id, btn) {
        $.ajax({
            url: `/admin/products/${id}/toggle-featured`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_featured) {
                        $(btn).removeClass('text-slate-300 dark:text-slate-600').addClass('text-amber-500 bg-amber-50 dark:bg-amber-950/50');
                        toastr.success('Marked as featured product');
                    } else {
                        $(btn).removeClass('text-amber-500 bg-amber-50 dark:bg-amber-950/50').addClass('text-slate-300 dark:text-slate-600');
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
                        $(btn).removeClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .addClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .html('<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span><span>Active</span>');
                        toastr.success('Product published');
                    } else {
                        $(btn).removeClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .addClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .html('<span class="w-2 h-2 rounded-full bg-slate-400"></span><span>Inactive</span>');
                        toastr.info('Product hidden');
                    }
                }
            }
        });
    }
</script>
@endpush
