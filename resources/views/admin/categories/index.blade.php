@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('content')
    <x-admin.page-header title="Categories" subtitle="Organise the catalog into bilingual English and Gujarati categories." icon="shapes">
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline"><i class="ph ph-tree-structure"></i> Subcategories</a>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add category</a>
    </x-admin.page-header>

    @php $noFilter = !request('status') && !request('featured'); @endphp
    <div class="stat-grid cols-4">
        <x-admin.stat-card label="All categories" :value="$stats['total'] ?? $categories->count()" icon="shapes" tone="slate"
            :href="route('admin.categories.index')" :active="$noFilter" meta="Total catalog" />
        <x-admin.stat-card label="Active" :value="$stats['active'] ?? 0" icon="check-circle" tone="emerald"
            :href="route('admin.categories.index', ['status' => 'active'])" :active="request('status') === 'active'" meta="Visible on the storefront" />
        <x-admin.stat-card label="Featured" :value="$stats['featured'] ?? 0" icon="star" tone="amber"
            :href="route('admin.categories.index', ['featured' => '1'])" :active="request('featured') == '1'" meta="Highlighted on home page" />
        <x-admin.stat-card label="Inactive" :value="$stats['inactive'] ?? 0" icon="pause-circle" tone="rose"
            :href="route('admin.categories.index', ['status' => 'inactive'])" :active="request('status') === 'inactive'" meta="Hidden from customers" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.categories.index') }}" class="filter-chip {{ $noFilter ? 'is-active' : '' }}">All</a>
                <a href="{{ route('admin.categories.index', ['status' => 'active']) }}" class="filter-chip {{ request('status') === 'active' ? 'is-active' : '' }}">Active</a>
                <a href="{{ route('admin.categories.index', ['status' => 'inactive']) }}" class="filter-chip {{ request('status') === 'inactive' ? 'is-active' : '' }}">Inactive</a>
                <a href="{{ route('admin.categories.index', ['featured' => '1']) }}" class="filter-chip {{ request('featured') == '1' ? 'is-active' : '' }}"><i class="ph-fill ph-star"></i> Featured</a>
            </div>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }}</span>
        </div>

        <table id="categoriesTable" class="w-full" data-export-title="Categories">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Category</th>
                    <th class="export-only">Slug</th>
                    <th>Subcategories</th>
                    <th>Products</th>
                    <th class="text-center">Featured</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td data-order="{{ $category->sort_order }}" data-export="{{ $category->sort_order }}">
                            <span class="badge badge-neutral font-mono">#{{ $category->sort_order }}</span>
                        </td>
                        <td data-export="{{ $category->name_en }}{{ $category->name_gu ? ' / '.$category->name_gu : '' }}">
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                @if($category->image)
                                    <img src="{{ $category->image_url }}" alt="" class="thumb" loading="lazy">
                                @else
                                    <span class="thumb flex items-center justify-center text-lg text-slate-600 dark:text-slate-300"><i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i></span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $category->name_en }}</a>
                                    @if($category->name_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" lang="gu">{{ $category->name_gu }}</span>@endif
                                    <span class="block font-mono text-[10.5px] text-slate-400 mt-0.5">/category/{{ $category->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $category->slug }}</td>
                        <td data-order="{{ $category->sub_categories_count }}" data-export="{{ $category->sub_categories_count }}">
                            <a href="{{ route('admin.subcategories.index', ['category_id' => $category->id]) }}" class="badge badge-violet hover:opacity-80">
                                <i class="ph ph-tree-structure"></i> {{ $category->sub_categories_count }} {{ \Illuminate\Support\Str::plural('sub', $category->sub_categories_count) }}
                            </a>
                        </td>
                        <td data-order="{{ $category->products_count }}" data-export="{{ $category->products_count }}">
                            <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="badge badge-info hover:opacity-80">
                                <i class="ph ph-package"></i> {{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}
                            </a>
                        </td>
                        <td class="text-center" data-order="{{ $category->is_featured ? 1 : 0 }}" data-export="{{ $category->is_featured ? 'Yes' : 'No' }}">
                            @if($category->is_featured)
                                <span class="badge badge-warning"><i class="ph-fill ph-star"></i> Featured</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td data-order="{{ $category->is_active ? 1 : 0 }}" data-export="{{ $category->is_active ? 'Active' : 'Inactive' }}">
                            <button type="button" onclick="toggleCategoryStatus({{ $category->id }}, this)" class="switch {{ $category->is_active ? 'is-on' : '' }}" role="switch" aria-checked="{{ $category->is_active ? 'true' : 'false' }}">
                                <span class="switch-track"></span><span class="switch-text">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                            </button>
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('categories.show', $category->slug) }}" target="_blank" class="act-btn" title="View on storefront"><i class="ph ph-arrow-square-out"></i></a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete “{{ $category->name_en }}”?" title="Delete"><i class="ph ph-trash"></i></button>
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
        $('#categoriesTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 2, visible: false },           // Slug: export-only
                { targets: [3, 4], responsivePriority: 3 }
            ]
        });
    });

    function toggleCategoryStatus(id, btn) {
        $.ajax({ url: `/admin/categories/${id}/toggle-status`, type: 'PATCH' })
            .done(function (res) {
                if (!res.success) return;
                const on = !!res.is_active;
                $(btn).toggleClass('is-on', on).attr('aria-checked', on ? 'true' : 'false').find('.switch-text').text(on ? 'Active' : 'Inactive');
                $(btn).closest('td').attr('data-export', on ? 'Active' : 'Inactive');
                toastr[on ? 'success' : 'info'](on ? 'Category activated' : 'Category deactivated');
            })
            .fail(function () { toastr.error('Could not update the category. Please try again.'); });
    }
</script>
@endpush
