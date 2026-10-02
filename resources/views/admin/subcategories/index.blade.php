@extends('admin.layouts.admin')

@section('title', 'Manage Sub Categories')
@section('page-title', 'Sub Categories')
@section('page-subtitle', 'Manage secondary level categories under main catalog categories')

@section('action-buttons')
<a href="{{ route('admin.subcategories.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
    <i class="fa-solid fa-plus"></i>
    <span>Add New Sub Category</span>
</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <!-- Category Filter Bar -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
        <form action="{{ route('admin.subcategories.index') }}" method="GET" class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-500 uppercase">Filter by Category:</label>
            <select name="category_id" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name_en }} ({{ $cat->name_gu }})
                    </option>
                @endforeach
            </select>
        </form>
        @if(request('category_id'))
            <a href="{{ route('admin.subcategories.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Clear Filter</a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table id="subCategoriesTable" class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <th class="p-3">Order</th>
                    <th class="p-3">Image</th>
                    <th class="p-3">Sub Category</th>
                    <th class="p-3">Parent Category</th>
                    <th class="p-3">Products Count</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($subCategories as $sub)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3 font-bold text-slate-400">#{{ $sub->sort_order }}</td>
                        <td class="p-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm">
                                <img src="{{ $sub->image_url }}" alt="{{ $sub->name_en }}" class="w-full h-full object-cover">
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-900 text-sm">{{ $sub->name_en }}</div>
                            <div class="text-xs text-brand-700 font-semibold">{{ $sub->name_gu }}</div>
                            <div class="text-[10px] text-slate-400">/subcategory/{{ $sub->slug }}</div>
                        </td>
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]">
                                <i class="fa-solid fa-folder-tree text-slate-400"></i>
                                <span>{{ $sub->category->name_en ?? 'N/A' }}</span>
                            </span>
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.products.index', ['sub_category_id' => $sub->id]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px]">
                                <i class="fa-solid fa-boxes-stacked"></i>
                                <span>{{ $sub->products_count }} Products</span>
                            </a>
                        </td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $sub->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $sub->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.subcategories.edit', $sub) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.subcategories.destroy', $sub) }}" method="POST" class="inline">
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
                        <td colspan="7" class="p-8 text-center text-slate-400">No subcategories found.</td>
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
        $('#subCategoriesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']]
        });
    });
</script>
@endpush
