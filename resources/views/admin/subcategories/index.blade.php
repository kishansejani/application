@extends('admin.layouts.admin')

@section('title', 'Sub Categories')

@section('content')
    <x-admin.page-header title="Subcategories" subtitle="Second-level groupings that sit under each parent category." icon="tree-structure">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline"><i class="ph ph-shapes"></i> Categories</a>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add subcategory</a>
    </x-admin.page-header>

    @php $noFilter = !request('status') && !request('category_id'); @endphp
    <div class="stat-grid cols-4">
        <x-admin.stat-card label="All subcategories" :value="$stats['total'] ?? $subCategories->count()" icon="tree-structure" tone="slate"
            :href="route('admin.subcategories.index')" :active="$noFilter" meta="Across all categories" />
        <x-admin.stat-card label="Active" :value="$stats['active'] ?? 0" icon="check-circle" tone="emerald"
            :href="route('admin.subcategories.index', array_filter(['status' => 'active', 'category_id' => request('category_id')]))" :active="request('status') === 'active'" meta="Visible on the storefront" />
        <x-admin.stat-card label="Inactive" :value="$stats['inactive'] ?? 0" icon="pause-circle" tone="rose"
            :href="route('admin.subcategories.index', array_filter(['status' => 'inactive', 'category_id' => request('category_id')]))" :active="request('status') === 'inactive'" meta="Hidden from customers" />
        <x-admin.stat-card label="Parent categories" :value="$stats['categories'] ?? 0" icon="shapes" tone="violet"
            :href="route('admin.categories.index')" meta="Manage categories" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <form action="{{ route('admin.subcategories.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5" data-no-loading>
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <select name="category_id" onchange="this.form.submit()" class="form-select w-auto min-w-[13rem]" aria-label="Filter by category">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }} ({{ $cat->name_gu }})</option>
                    @endforeach
                </select>
                @if(!$noFilter)
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-ghost btn-sm text-rose-600"><i class="ph ph-x-circle"></i> Clear filters</a>
                @endif
            </form>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $subCategories->count() }} {{ \Illuminate\Support\Str::plural('subcategory', $subCategories->count()) }}</span>
        </div>

        <table id="subCategoriesTable" class="w-full" data-export-title="Subcategories">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Subcategory</th>
                    <th class="export-only">Slug</th>
                    <th>Parent category</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subCategories as $sub)
                    <tr>
                        <td data-order="{{ $sub->sort_order }}" data-export="{{ $sub->sort_order }}">
                            <span class="badge badge-neutral font-mono">#{{ $sub->sort_order }}</span>
                        </td>
                        <td data-export="{{ $sub->name_en }}{{ $sub->name_gu ? ' / '.$sub->name_gu : '' }}">
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                <img src="{{ $sub->image_url }}" alt="" class="thumb" loading="lazy">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.subcategories.edit', $sub) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $sub->name_en }}</a>
                                    @if($sub->name_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" lang="gu">{{ $sub->name_gu }}</span>@endif
                                    <span class="block font-mono text-[10.5px] text-slate-400 mt-0.5">/subcategory/{{ $sub->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $sub->slug }}</td>
                        <td data-export="{{ $sub->category->name_en ?? 'N/A' }}">
                            @if($sub->category)
                                <a href="{{ route('admin.subcategories.index', ['category_id' => $sub->category_id]) }}" class="badge badge-neutral hover:opacity-80" title="Show only this category">
                                    <i class="{{ $sub->category->icon ?: 'fa-solid fa-layer-group' }}"></i> {{ $sub->category->name_en }}
                                </a>
                            @else
                                <span class="badge badge-neutral">N/A</span>
                            @endif
                        </td>
                        <td data-order="{{ $sub->products_count }}" data-export="{{ $sub->products_count }}">
                            <a href="{{ route('admin.products.index', ['sub_category_id' => $sub->id]) }}" class="badge badge-info hover:opacity-80">
                                <i class="ph ph-package"></i> {{ $sub->products_count }} {{ \Illuminate\Support\Str::plural('product', $sub->products_count) }}
                            </a>
                        </td>
                        <td data-order="{{ $sub->is_active ? 1 : 0 }}" data-export="{{ $sub->is_active ? 'Active' : 'Inactive' }}">
                            @if($sub->is_active)
                                <span class="badge badge-success badge-dot">Active</span>
                            @else
                                <span class="badge badge-neutral badge-dot">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('subcategories.show', $sub->slug) }}" target="_blank" class="act-btn" title="View on storefront"><i class="ph ph-arrow-square-out"></i></a>
                                <a href="{{ route('admin.subcategories.edit', $sub) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                                <form action="{{ route('admin.subcategories.destroy', $sub) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete “{{ $sub->name_en }}”?" title="Delete"><i class="ph ph-trash"></i></button>
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
        $('#subCategoriesTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 2, visible: false },           // Slug: export-only
                { targets: [3, 4], responsivePriority: 3 }
            ]
        });
    });
</script>
@endpush
