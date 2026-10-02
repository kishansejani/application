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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Card 1: All Categories -->
        <a href="{{ route('admin.categories.index') }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ !request('status') && !request('featured') ? 'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-md border border-slate-700/50' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ !request('status') && !request('featured') ? 'text-slate-300' : 'text-slate-500 dark:text-slate-400' }}">All Categories</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ !request('status') && !request('featured') ? 'bg-white/10 text-indigo-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                    <i class="fa-solid fa-shapes"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ !request('status') && !request('featured') ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['total'] ?? $categories->count() }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ !request('status') && !request('featured') ? 'bg-white/10 text-slate-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">Total</span>
            </div>
            <i class="fa-solid fa-shapes absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 2: Active Categories -->
        <a href="{{ route('admin.categories.index', ['status' => 'active']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('status') == 'active' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md border border-emerald-500' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-emerald-500 shadow-sm hover:border-emerald-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ request('status') == 'active' ? 'text-emerald-100' : 'text-emerald-700 dark:text-emerald-400' }}">Active Categories</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('status') == 'active' ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' }}">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ request('status') == 'active' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['active'] ?? 0 }}</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full flex items-center gap-1 {{ request('status') == 'active' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                </span>
            </div>
            <i class="fa-solid fa-circle-check absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 3: Featured -->
        <a href="{{ route('admin.categories.index', ['featured' => '1']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('featured') == '1' ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md border border-amber-400' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-amber-500 shadow-sm hover:border-amber-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ request('featured') == '1' ? 'text-amber-100' : 'text-amber-700 dark:text-amber-400' }}">Featured</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('featured') == '1' ? 'bg-white/20 text-white' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' }}">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ request('featured') == '1' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['featured'] ?? 0 }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ request('featured') == '1' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300' }}">★ Starred</span>
            </div>
            <i class="fa-solid fa-star absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 4: Inactive -->
        <a href="{{ route('admin.categories.index', ['status' => 'inactive']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('status') == 'inactive' ? 'bg-gradient-to-br from-rose-600 to-red-700 text-white shadow-md border border-rose-500' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-rose-500 shadow-sm hover:border-rose-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ request('status') == 'inactive' ? 'text-rose-100' : 'text-rose-700 dark:text-rose-400' }}">Inactive</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('status') == 'inactive' ? 'bg-white/20 text-white' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }}">
                    <i class="fa-solid fa-circle-pause"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ request('status') == 'inactive' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['inactive'] ?? 0 }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ request('status') == 'inactive' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300' }}">Hidden</span>
            </div>
            <i class="fa-solid fa-circle-pause absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
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
