@extends('frontend.layouts.app')

@section('title', $subcategory->localized_name . ' - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5">

    <nav class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-400 overflow-x-auto no-scrollbar whitespace-nowrap" aria-label="{{ __('messages.breadcrumb') }}">
        <a href="{{ route('home') }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ __('messages.home') }}</a>
        <i class="ph-bold ph-caret-right text-[10px]"></i>
        <a href="{{ route('categories.show', $subcategory->category->slug) }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ $subcategory->category->localized_name }}</a>
        <i class="ph-bold ph-caret-right text-[10px]"></i>
        <span class="text-slate-700 dark:text-slate-200">{{ $subcategory->localized_name }}</span>
    </nav>

    <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-900 dark:to-slate-950 dark:border dark:border-slate-800 text-white p-5 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-center gap-4 sm:gap-5">
            <span class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 overflow-hidden shrink-0 ring-1 ring-white/15">
                <img src="{{ $subcategory->image_url }}" alt="" class="w-full h-full object-cover">
            </span>
            <div class="min-w-0">
                <a href="{{ route('categories.show', $subcategory->category->slug) }}" class="inline-flex items-center gap-1 text-[11px] text-brand-300 font-bold uppercase tracking-wider hover:underline"><i class="ph-bold ph-arrow-left"></i>{{ $subcategory->category->localized_name }}</a>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">{{ $subcategory->localized_name }}</h1>
                @if($subcategory->localized_description)<p class="text-[13px] sm:text-sm text-slate-300 mt-1 line-clamp-2">{{ $subcategory->localized_description }}</p>@endif
            </div>
        </div>
        <div class="bg-white/10 px-4 py-3 rounded-2xl text-[12px] font-semibold flex items-start gap-2 md:max-w-xs">
            <i class="ph-fill ph-lightning text-amber-400 text-base shrink-0"></i>
            <span>{{ $gu ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</span>
        </div>
    </header>

    @if($subcategory->category->subCategories->count() > 1)
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar -mx-3 px-3 sm:mx-0 sm:px-0 pb-1">
            <a href="{{ route('categories.show', $subcategory->category->slug) }}" class="shrink-0 px-4 py-2 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[12px] font-semibold text-slate-700 dark:text-slate-200 hover:border-brand-400">{{ $gu ? 'બધું' : 'All' }}</a>
            @foreach($subcategory->category->subCategories as $sib)
                <a href="{{ route('subcategories.show', $sib->slug) }}" class="shrink-0 px-4 py-2 rounded-full text-[12px] {{ $sib->id === $subcategory->id ? 'bg-brand-600 text-white font-bold' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-200 hover:border-brand-400' }}">{{ $sib->localized_name }}</a>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-5">
        @forelse($subcategory->products as $product)
            @include('frontend.partials.product-card', ['product' => $product, 'showCategory' => false])
        @empty
            <div class="col-span-full fx-card py-14 text-center">
                <i class="ph-duotone ph-basket text-5xl text-slate-300 dark:text-slate-600"></i>
                <h4 class="mt-3 font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'આ પેટા શ્રેણીમાં હજી કોઈ ઉત્પાદન નથી' : 'No products in this subcategory yet' }}</h4>
                <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary mt-4">{{ $gu ? 'બધા ઉત્પાદનો' : 'Browse all products' }}</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
