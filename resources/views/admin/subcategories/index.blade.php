@extends('admin.layouts.admin')

@section('title', 'Sub Categories')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Sub Categories</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage secondary level catalog categories under parent categories.</p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Sub Category</span>
        </a>
    </div>

    <!-- Top KPI / Pipeline Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.subcategories.index') }}" class="p-4 rounded-2xl border transition-all {{ !request('status') && !request('category_id') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-slate-900 dark:border-white shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">All Sub Categories</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['total'] ?? $subCategories->count() }}</h4>
        </a>

        <a href="{{ route('admin.subcategories.index', ['status' => 'active']) }}" class="p-4 rounded-2xl border transition-all {{ request('status') == 'active' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Active Subs</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['active'] ?? 0 }}</h4>
        </a>

        <a href="{{ route('admin.subcategories.index', ['status' => 'inactive']) }}" class="p-4 rounded-2xl border transition-all {{ request('status') == 'inactive' ? 'bg-rose-600 text-white border-rose-600 shadow-md' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-rose-50 dark:hover:bg-rose-950/30' }}">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Inactive Subs</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['inactive'] ?? 0 }}</h4>
        </a>

        <a href="{{ route('admin.categories.index') }}" class="p-4 rounded-2xl border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
            <p class="text-[10px] font-bold uppercase tracking-wider opacity-75 text-indigo-600 dark:text-indigo-400">📁 Parent Categories</p>
            <h4 class="text-xl font-extrabold mt-1">{{ $stats['categories'] ?? 0 }}</h4>
        </a>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <!-- Category Filter Bar -->
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700/60">
            <form action="{{ route('admin.subcategories.index') }}" method="GET" class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Filter by Category:</label>
                <select name="category_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-slate-400 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_en }} ({{ $cat->name_gu }})
                        </option>
                    @endforeach
                </select>
            </form>
            @if(request('category_id'))
                <a href="{{ route('admin.subcategories.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Clear Filter
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table id="subCategoriesTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Order</th>
                        <th class="px-4 py-3.5">Image</th>
                        <th class="px-4 py-3.5">Sub Category</th>
                        <th class="px-4 py-3.5">Parent Category</th>
                        <th class="px-4 py-3.5">Products Count</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($subCategories as $sub)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-400">
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded-lg text-[11px] text-slate-600 dark:text-slate-300">#{{ $sub->sort_order }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="w-12 h-12 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 shadow-sm flex items-center justify-center text-slate-700 dark:text-slate-200">
                                    <img src="{{ $sub->image_url }}" alt="{{ $sub->name_en }}" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $sub->name_en }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $sub->name_gu }}</div>
                                <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">/subcategory/{{ $sub->slug }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px]">
                                    <i class="fa-solid fa-folder-tree text-slate-400"></i>
                                    <span>{{ $sub->category->name_en ?? 'N/A' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.products.index', ['sub_category_id' => $sub->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 font-bold text-[11px] transition shadow-sm border border-emerald-100 dark:border-emerald-800">
                                    <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                                    <span>{{ $sub->products_count }} Products</span>
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm border {{ $sub->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $sub->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span>{{ $sub->is_active ? 'Active' : 'Inactive' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.subcategories.edit', $sub) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Edit Sub Category">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.subcategories.destroy', $sub) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="confirm-delete-btn w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Delete Sub Category">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">No subcategories found.</td>
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
        $('#subCategoriesTable').DataTable({
            order: [[0, 'asc']]
        });
    });
</script>
@endpush
