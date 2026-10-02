@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Product Categories</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage catalog categories with multilingual English & Gujarati titles.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Category</span>
        </a>
    </div>

    <!-- Top KPI / Pipeline Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.categories.index') }}" class="p-4 rounded-2xl border transition-all {{ !request('status') && !request('featured') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-slate-900 dark:border-white shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">All Categories</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['total'] ?? $categories->count() }}</h4>
        </a>

        <a href="{{ route('admin.categories.index', ['status' => 'active']) }}" class="p-4 rounded-2xl border transition-all {{ request('status') == 'active' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Active Categories</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['active'] ?? 0 }}</h4>
        </a>

        <a href="{{ route('admin.categories.index', ['featured' => '1']) }}" class="p-4 rounded-2xl border transition-all {{ request('featured') == '1' ? 'bg-amber-500 text-white border-amber-500 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-amber-50 dark:hover:bg-amber-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">⭐ Featured</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['featured'] ?? 0 }}</h4>
        </a>

        <a href="{{ route('admin.categories.index', ['status' => 'inactive']) }}" class="p-4 rounded-2xl border transition-all {{ request('status') == 'inactive' ? 'bg-rose-600 text-white border-rose-600 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-rose-50 dark:hover:bg-rose-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Inactive Categories</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['inactive'] ?? 0 }}</h4>
        </a>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="categoriesTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Order</th>
                        <th class="px-4 py-3.5">Icon / Image</th>
                        <th class="px-4 py-3.5">Category Name</th>
                        <th class="px-4 py-3.5">Subcategories</th>
                        <th class="px-4 py-3.5">Products</th>
                        <th class="px-4 py-3.5">Featured</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-400">
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded-lg text-[11px] text-slate-600 dark:text-slate-300">#{{ $category->sort_order }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="w-12 h-12 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-700 dark:text-slate-200 text-lg shadow-sm">
                                    @if($category->image)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name_en }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $category->name_en }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $category->name_gu }}</div>
                                <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">/category/{{ $category->slug }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.subcategories.index', ['category_id' => $category->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 font-bold text-[11px] transition shadow-sm border border-indigo-100 dark:border-indigo-800">
                                    <i class="fa-solid fa-sitemap text-[10px]"></i>
                                    <span>{{ $category->sub_categories_count }} Subs</span>
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 font-bold text-[11px] transition shadow-sm border border-emerald-100 dark:border-emerald-800">
                                    <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                                    <span>{{ $category->products_count }} Products</span>
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($category->is_featured)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i class="fa-solid fa-star text-[9px]"></i> Featured
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <button onclick="toggleCategoryStatus({{ $category->id }}, this)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm border {{ $category->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span>{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Edit Category">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="confirm-delete-btn w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Delete Category">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">No categories found.</td>
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
        $('#categoriesTable').DataTable({
            order: [[0, 'asc']]
        });
    });

    function toggleCategoryStatus(id, btn) {
        $.ajax({
            url: `/admin/categories/${id}/toggle-status`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_active) {
                        $(btn).removeClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .addClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .html('<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span><span>Active</span>');
                        toastr.success('Category activated');
                    } else {
                        $(btn).removeClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .addClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .html('<span class="w-2 h-2 rounded-full bg-slate-400"></span><span>Inactive</span>');
                        toastr.info('Category deactivated');
                    }
                }
            }
        });
    }
</script>
@endpush
