@props(['label', 'value', 'icon' => 'chart-bar', 'tone' => 'slate', 'href' => null, 'active' => false, 'meta' => null, 'metaIcon' => null])
{{-- KPI tile. Renders as a link when href is provided (used as a quick filter). --}}
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'stat-card'.($active ? ' is-active' : '')]) }}>
    <div class="stat-top">
        <span class="stat-label">{{ __($label) }}</span>
        <span class="stat-icon tone-{{ $tone }}"><i class="ph-duotone ph-{{ $icon }}"></i></span>
    </div>
    <div class="stat-value">{{ $value }}</div>
    @if($meta || trim($slot) !== '')
        <div class="stat-meta">
            @if($metaIcon)<i class="ph ph-{{ $metaIcon }}"></i>@endif
            {{ is_string($meta) ? __($meta) : $meta }}{{ $slot }}
        </div>
    @endif
</{{ $tag }}>
