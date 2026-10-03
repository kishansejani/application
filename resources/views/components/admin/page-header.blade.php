@props(['title', 'subtitle' => null, 'icon' => null])
{{-- Standard admin page header: <x-admin.page-header title="Products" subtitle="..." icon="package"> actions… </x-admin.page-header> --}}
<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="page-header-main">
        @if($icon)
            <span class="page-header-icon"><i class="ph-duotone ph-{{ $icon }}"></i></span>
        @endif
        <div class="min-w-0">
            <h1 class="page-title">{{ __($title) }}</h1>
            @if($subtitle)
                <p class="page-subtitle">{{ __($subtitle) }}</p>
            @endif
        </div>
    </div>
    @if(trim($slot) !== '')
        <div class="page-actions">{{ $slot }}</div>
    @endif
</div>
