@extends('frontend.layouts.app')

@section('title', __('messages.products') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $activeCat = request('category') ? $categories->firstWhere('slug', request('category')) : null;
    $hasFilters = request()->hasAny(['category', 'subcategory', 'q', 'min_price', 'max_price']);
    $sortOptions = [
        'featured' => $gu ? 'વિશેષ' : 'Featured',
        'price_asc' => $gu ? 'કિંમત: ઓછી થી વધુ' : 'Price: low to high',
        'price_desc' => $gu ? 'કિંમત: વધુ થી ઓછી' : 'Price: high to low',
        'newest' => $gu ? 'નવી આવક' : 'Newest arrivals',
    ];
@endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <!-- Breadcrumb + heading -->
    <nav class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-400 mb-2" aria-label="{{ __('messages.breadcrumb') }}">
        <a href="{{ route('home') }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ __('messages.home') }}</a>
        <i class="ph-bold ph-caret-right text-[10px]"></i>
        <span class="text-slate-700 dark:text-slate-200">{{ __('messages.products') }}</span>
    </nav>
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                @if(request('q'))
                    {{ $gu ? 'શોધ પરિણામ' : 'Results for' }} “{{ request('q') }}”
                @elseif($activeCat)
                    {{ $activeCat->localized_name }}
                @else
                    {{ __('messages.products') }}
                @endif
            </h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $products->total() }} {{ $gu ? 'વસ્તુઓ · ૨ કલાક એક્સપ્રેસ ડિલિવરી' : 'items · 2-hour express delivery' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" data-open="filterSheet" class="lg:hidden fx-btn fx-btn-outline !h-10 flex-1 sm:flex-none">
                <i class="ph ph-sliders-horizontal text-lg"></i>{{ __('messages.filter_by') }}
                @if($hasFilters)<span class="w-2 h-2 rounded-full bg-brand-500"></span>@endif
            </button>
            <form action="{{ route('products.index') }}" method="GET" class="flex-1 sm:flex-none">
                @foreach(['category', 'subcategory', 'q', 'min_price', 'max_price'] as $keep)
                    @if(request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
                @endforeach
                <label class="relative block">
                    <span class="sr-only">{{ __('messages.sort_by') }}</span>
                    <i class="ph ph-arrows-down-up absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <select name="sort" onchange="this.form.submit()" class="fx-input !h-10 !py-0 !pl-9 !text-[13px] font-semibold sm:min-w-[12rem]">
                        @foreach($sortOptions as $val => $label)
                            <option value="{{ $val }}" {{ request('sort', 'featured') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </form>
        </div>
    </div>

    <!-- Active filter chips -->
    @if($hasFilters)
        <div class="flex flex-wrap items-center gap-2 mb-5">
            @if(request('q'))
                <a href="{{ route('products.index', request()->except(['q', 'page'])) }}" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[12px] font-semibold text-slate-700 dark:text-slate-200 hover:border-rose-300">
                    <i class="ph ph-magnifying-glass"></i>{{ request('q') }}<i class="ph-bold ph-x text-slate-400"></i>
                </a>
            @endif
            @if($activeCat)
                <a href="{{ route('products.index', request()->except(['category', 'subcategory', 'page'])) }}" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[12px] font-semibold text-slate-700 dark:text-slate-200 hover:border-rose-300">
                    {{ $activeCat->localized_name }}<i class="ph-bold ph-x text-slate-400"></i>
                </a>
            @endif
            @if(request('min_price') || request('max_price'))
                <a href="{{ route('products.index', request()->except(['min_price', 'max_price', 'page'])) }}" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[12px] font-semibold text-slate-700 dark:text-slate-200 hover:border-rose-300">
                    ₹{{ request('min_price') ?: 0 }} – {{ request('max_price') ? '₹' . request('max_price') : '∞' }}<i class="ph-bold ph-x text-slate-400"></i>
                </a>
            @endif
            <a href="{{ route('products.index') }}" class="text-[12px] font-bold text-rose-600 dark:text-rose-400 hover:underline ml-1">{{ __('messages.clear_all') }}</a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 xl:gap-8 items-start">
        <!-- Sidebar (desktop) -->
        <aside class="hidden lg:block fx-card p-5 sticky top-36">
            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2 mb-5">
                <i class="ph ph-funnel text-brand-600"></i>{{ __('messages.filter_by') }}
            </h4>
            @include('frontend.products._filters')
        </aside>

        <!-- Grid -->
        <div class="lg:col-span-3 space-y-8">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-5">
                @forelse($products as $product)
                    @include('frontend.partials.product-card', ['product' => $product])
                @empty
                    <div class="col-span-full fx-card py-16 px-6 text-center">
                        <span class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mb-4"><i class="ph-duotone ph-magnifying-glass"></i></span>
                        <h4 class="font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'કોઈ ઉત્પાદન મળ્યું નથી' : 'No products match your filters' }}</h4>
                        <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'ફિલ્ટર દૂર કરો અથવા બીજું કંઈક શોધો.' : 'Try clearing some filters or searching for something else.' }}</p>
                        <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary mt-5">{{ $gu ? 'ફિલ્ટર રીસેટ કરો' : 'Reset filters' }}</a>
                    </div>
                @endforelse
            </div>

            {{ $products->links('frontend.partials.pagination') }}
        </div>
    </div>
</div>

<!-- Filter sheet (mobile / tablet) -->
<div id="filterSheet" class="fx-overlay lg:hidden" aria-hidden="true">
    <div class="fx-backdrop" data-close="filterSheet"></div>
    <div class="fx-panel fx-panel-center fx-sheet-mobile outline-none" style="--fx-modal-w: 440px" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="filterSheetTitle">
        <div class="flex items-center justify-between px-5 h-14 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <h3 id="filterSheetTitle" class="font-extrabold text-slate-900 dark:text-white flex items-center gap-2"><i class="ph ph-funnel text-brand-600"></i>{{ __('messages.filter_by') }}</h3>
            <button type="button" class="fx-icon-btn" data-close="filterSheet" aria-label="{{ __('messages.close') }}"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="overflow-y-auto p-5">
            @include('frontend.products._filters')
        </div>
    </div>
</div>
@endsection
