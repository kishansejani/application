@extends('admin.layouts.admin')

@section('title', 'Manage Categories')
@section('page-title', 'Product Categories')
@section('page-subtitle', 'Manage root product categories with English and Gujarati titles')

@section('action-buttons')
<a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
    <i class="fa-solid fa-plus"></i>
    <span>Add New Category</span>
</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="overflow-x-auto">
        <table id="categoriesTable" class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <th class="p-3">Order</th>
                    <th class="p-3">Icon / Image</th>
                    <th class="p-3">Category Name</th>
                    <th class="p-3">Subcategories</th>
                    <th class="p-3">Products</th>
                    <th class="p-3">Featured</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($categories as $category)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3 font-bold text-slate-400">#{{ $category->sort_order }}</td>
                        <td class="p-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center text-brand-600 text-xl shadow-sm">
                                @if($category->image)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name_en }}" class="w-full h-full object-cover">
                                @else
                                    <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i>
                                @endif
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-900 text-sm">{{ $category->name_en }}</div>
                            <div class="text-xs text-brand-700 font-semibold">{{ $category->name_gu }}</div>
                            <div class="text-[10px] text-slate-400">/category/{{ $category->slug }}</div>
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.subcategories.index', ['category_id' => $category->id]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-[11px] transition-colors">
                                <i class="fa-solid fa-sitemap"></i>
                                <span>{{ $category->sub_categories_count }} Subs</span>
                            </a>
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition-colors">
                                <i class="fa-solid fa-boxes-stacked"></i>
                                <span>{{ $category->products_count }} Products</span>
                            </a>
                        </td>
                        <td class="p-3">
                            @if($category->is_featured)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Featured</span>
                            @else
                                <span class="text-slate-400 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <button onclick="toggleCategoryStatus({{ $category->id }}, this)" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all {{ $category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
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
                        <td colspan="8" class="p-8 text-center text-slate-400">No categories found.</td>
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
        $('#categoriesTable').DataTable({
            responsive: true,
            pageLength: 10,
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
                        $(btn).removeClass('bg-slate-100 text-slate-500').addClass('bg-emerald-100 text-emerald-800').text('Active');
                        toastr.success('Category activated');
                    } else {
                        $(btn).removeClass('bg-emerald-100 text-emerald-800').addClass('bg-slate-100 text-slate-500').text('Inactive');
                        toastr.info('Category deactivated');
                    }
                }
            }
        });
    }
</script>
@endpush
