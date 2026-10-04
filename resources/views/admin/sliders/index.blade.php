@extends('admin.layouts.admin')

@section('title', 'Home Sliders')

@section('content')
    <x-admin.page-header title="Home sliders" subtitle="Promotional hero banners, sale badges and their link destinations." icon="slideshow">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline"><i class="ph ph-storefront"></i> Storefront</a>
        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add banner</a>
    </x-admin.page-header>

    @php
        // Resolve link targets for display (small tables, one query per type)
        $ids = fn ($type) => $sliders->where('link_type', $type)->pluck('target_id')->filter()->unique()->values();
        $targetNames = [
            'category' => \App\Models\Category::whereIn('id', $ids('category'))->pluck('name_en', 'id'),
            'product'  => \App\Models\Product::whereIn('id', $ids('product'))->pluck('name_en', 'id'),
            'offer'    => \App\Models\Offer::whereIn('id', $ids('offer'))->pluck('title_en', 'id'),
        ];
        $linkMeta = [
            'none'     => ['No link', 'badge-neutral', 'prohibit'],
            'category' => ['Category', 'badge-violet', 'shapes'],
            'product'  => ['Product', 'badge-info', 'package'],
            'offer'    => ['Offer', 'badge-warning', 'ticket'],
            'custom'   => ['Custom URL', 'badge-neutral', 'link-simple'],
        ];
    @endphp

    <div class="stat-grid cols-4">
        <x-admin.stat-card label="All banners" :value="$stats['total'] ?? $sliders->count()" icon="slideshow" tone="slate"
            :href="route('admin.sliders.index')" :active="!request('status')" meta="In the home slider" />
        <x-admin.stat-card label="Active" :value="$stats['active'] ?? 0" icon="check-circle" tone="emerald"
            :href="route('admin.sliders.index', ['status' => 'active'])" :active="request('status') === 'active'" meta="Currently rotating" />
        <x-admin.stat-card label="Inactive" :value="$stats['inactive'] ?? 0" icon="pause-circle" tone="rose"
            :href="route('admin.sliders.index', ['status' => 'inactive'])" :active="request('status') === 'inactive'" meta="Hidden from the home page" />
        <x-admin.stat-card label="With promo badge" :value="$stats['with_badge'] ?? 0" icon="tag" tone="amber" meta="Show a sale label" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.sliders.index') }}" class="filter-chip {{ !request('status') ? 'is-active' : '' }}">All</a>
                <a href="{{ route('admin.sliders.index', ['status' => 'active']) }}" class="filter-chip {{ request('status') === 'active' ? 'is-active' : '' }}">Active</a>
                <a href="{{ route('admin.sliders.index', ['status' => 'inactive']) }}" class="filter-chip {{ request('status') === 'inactive' ? 'is-active' : '' }}">Inactive</a>
            </div>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $sliders->count() }} {{ \Illuminate\Support\Str::plural('banner', $sliders->count()) }}</span>
        </div>

        <table id="slidersTable" class="w-full" data-export-title="Home sliders">
            <thead>
                <tr>
                    <th style="width: 70px;">Sr. No</th>
                    <th>Banner</th>
                    <th class="export-only">Subtitle</th>
                    <th>Badge</th>
                    <th>Links to</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sliders as $slider)
                    @php
                        [$ltLabel, $ltBadge, $ltIcon] = $linkMeta[$slider->link_type] ?? [ucfirst($slider->link_type ?? 'none'), 'badge-neutral', 'link-simple'];
                        $target = $slider->link_type === 'custom'
                            ? $slider->link_url
                            : ($targetNames[$slider->link_type][$slider->target_id] ?? ($slider->target_id ? '#'.$slider->target_id : null));
                    @endphp
                    <tr>
                        <td data-order="{{ $slider->sort_order }}" data-export="{{ $slider->sort_order }}">
                            <span class="badge badge-neutral font-mono font-bold">{{ $slider->sort_order }}</span>
                        </td>
                        <td data-export="{{ $slider->title_en ?: 'Untitled banner' }}{{ $slider->title_gu ? ' / '.$slider->title_gu : '' }}">
                            <div class="flex items-center gap-3 min-w-[16rem]">
                                <img src="{{ $slider->image_url }}" alt="" class="w-28 h-14 rounded-xl object-cover border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 shrink-0" loading="lazy">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.sliders.edit', $slider) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $slider->title_en ?: 'Untitled banner' }}</a>
                                    @if($slider->title_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" lang="gu">{{ $slider->title_gu }}</span>@endif
                                    @if($slider->subtitle_en)<span class="block text-[11px] text-slate-400 mt-0.5 truncate max-w-[18rem]">{{ $slider->subtitle_en }}</span>@endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $slider->subtitle_en }}</td>
                        <td data-export="{{ $slider->badge_en ?: '—' }}">
                            @if($slider->badge_en)
                                <span class="badge badge-warning"><i class="ph-fill ph-tag"></i> {{ $slider->badge_en }}</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td data-export="{{ $ltLabel }}{{ $target ? ': '.$target : '' }}">
                            <span class="badge {{ $ltBadge }}"><i class="ph ph-{{ $ltIcon }}"></i> {{ $ltLabel }}</span>
                            @if($target)<span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-1 truncate max-w-[12rem]">{{ $target }}</span>@endif
                        </td>
                        <td data-order="{{ $slider->is_active ? 1 : 0 }}" data-export="{{ $slider->is_active ? 'Active' : 'Inactive' }}">
                            <button type="button" onclick="toggleSliderStatus({{ $slider->id }}, this)" class="switch {{ $slider->is_active ? 'is-on' : '' }}" role="switch" aria-checked="{{ $slider->is_active ? 'true' : 'false' }}">
                                <span class="switch-track"></span><span class="switch-text">{{ $slider->is_active ? 'Active' : 'Inactive' }}</span>
                            </button>
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('admin.sliders.edit', $slider) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                                <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete “{{ $slider->title_en ?: 'this banner' }}”?" title="Delete"><i class="ph ph-trash"></i></button>
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
        $('#slidersTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 2, visible: false },           // Subtitle: export-only
                { targets: [3, 4], responsivePriority: 3 }
            ]
        });
    });

    function toggleSliderStatus(id, btn) {
        $.ajax({ url: `/admin/sliders/${id}/toggle-status`, type: 'PATCH' })
            .done(function (res) {
                if (!res.success) return;
                const on = !!res.is_active;
                $(btn).toggleClass('is-on', on).attr('aria-checked', on ? 'true' : 'false').find('.switch-text').text(on ? 'Active' : 'Inactive');
                $(btn).closest('td').attr('data-export', on ? 'Active' : 'Inactive');
                toastr[on ? 'success' : 'info'](on ? 'Slider banner activated' : 'Slider banner deactivated');
            })
            .fail(function () { toastr.error('Could not update the banner. Please try again.'); });
    }
</script>
@endpush
