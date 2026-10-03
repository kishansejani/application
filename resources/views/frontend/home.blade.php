@extends('frontend.layouts.app')

@section('title', __('messages.store_name') . ' - ' . __('messages.tagline'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="space-y-10 sm:space-y-14 pb-6">

    <!-- Hero slider -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6">
        @if($sliders->count())
            <div class="relative rounded-3xl overflow-hidden bg-slate-900 shadow-xl shadow-slate-900/10" data-slider aria-roledescription="carousel">
                <div class="fx-slider-track">
                    @foreach($sliders as $slider)
                        <div class="relative h-56 xs:h-64 sm:h-80 lg:h-[420px]" aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ $sliders->count() }}">
                            <img src="{{ $slider->image_url }}" alt="{{ $slider->localized_title }}" class="absolute inset-0 w-full h-full object-cover" @if(!$loop->first) loading="lazy" @endif>
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/55 to-slate-950/0"></div>
                            <div class="relative h-full flex items-center">
                                <div class="max-w-xl px-5 sm:px-10 lg:px-14 space-y-2.5 sm:space-y-4 text-white">
                                    @if($slider->localized_badge)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-400 text-slate-950 font-extrabold text-[10px] sm:text-[11px] uppercase tracking-wider rounded-full">
                                            <i class="ph-fill ph-lightning"></i>{{ $slider->localized_badge }}
                                        </span>
                                    @endif
                                    <h2 class="text-[22px] leading-tight xs:text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight line-clamp-2">{{ $slider->localized_title }}</h2>
                                    @if($slider->localized_subtitle)
                                        <p class="text-[13px] sm:text-base text-slate-200 line-clamp-2 max-w-md">{{ $slider->localized_subtitle }}</p>
                                    @endif
                                    <div class="pt-1 sm:pt-2">
                                        <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary sm:!px-6 sm:!py-3 !rounded-xl">
                                            <span>{{ __('messages.start_shopping') }}</span><i class="ph-bold ph-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($sliders->count() > 1)
                    <div class="absolute bottom-3 sm:bottom-5 left-5 sm:left-10 lg:left-14 flex items-center gap-1.5">
                        @foreach($sliders as $slider)
                            <button type="button" data-dot class="fx-dot {{ $loop->first ? 'is-active' : '' }}" aria-label="{{ __('messages.slide', ['n' => $loop->iteration]) }}"></button>
                        @endforeach
                    </div>
                    <div class="hidden sm:flex absolute bottom-4 right-4 gap-2">
                        <button type="button" data-prev class="w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center" aria-label="{{ __('messages.prev_slide') }}"><i class="ph-bold ph-caret-left"></i></button>
                        <button type="button" data-next class="w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center" aria-label="{{ __('messages.next_slide') }}"><i class="ph-bold ph-caret-right"></i></button>
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-3xl bg-gradient-to-br from-brand-600 to-teal-700 text-white p-8 sm:p-14">
                <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight max-w-xl">{{ __('messages.tagline') }}</h2>
                <a href="{{ route('products.index') }}" class="mt-6 fx-btn bg-white text-brand-800">{{ __('messages.start_shopping') }}<i class="ph-bold ph-arrow-right"></i></a>
            </div>
        @endif
    </section>

    <!-- Delivery promise -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 -mt-6 sm:-mt-9">
        <div class="fx-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex items-center gap-3.5 flex-1 min-w-0">
                <span class="w-12 h-12 rounded-2xl {{ $deliverySlotInfo['type'] === 'two_hours' ? 'bg-amber-50 text-amber-500 dark:bg-amber-500/10' : 'bg-sky-50 text-sky-500 dark:bg-sky-500/10' }} flex items-center justify-center text-2xl shrink-0">
                    <i class="ph-fill {{ $deliverySlotInfo['type'] === 'two_hours' ? 'ph-lightning' : 'ph-calendar-check' }}"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">{{ trim(str_replace('⚡', '', __('messages.delivery_promise_title'))) }}</p>
                    <p class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white leading-snug">{{ $gu ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</p>
                    <p class="text-[12px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('messages.delivery_promise_desc') }}</p>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="fx-btn fx-btn-dark shrink-0 w-full md:w-auto">{{ $gu ? 'ઝડપી ડિલિવરી માટે ઓર્ડર કરો' : 'Order for quick delivery' }}<i class="ph-bold ph-arrow-right"></i></a>
        </div>
    </section>

    <!-- Categories -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" aria-labelledby="homeCats">
        <div class="flex items-end justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <h2 id="homeCats" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.categories') }}</h2>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $gu ? 'તાજી ખેત પેદાશો અને ઘરની જરૂરિયાતો' : 'Farm-fresh produce and household essentials' }}</p>
            </div>
            <a href="{{ route('categories.index') }}" class="text-[13px] font-bold text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1 shrink-0">{{ $gu ? 'બધું જુઓ' : 'See all' }}<i class="ph-bold ph-arrow-right"></i></a>
        </div>
        <div class="grid grid-cols-3 xs:grid-cols-3 sm:grid-cols-6 gap-2.5 sm:gap-4">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="group fx-card p-3 sm:p-4 flex flex-col items-center text-center hover:border-brand-400 dark:hover:border-brand-600 hover:-translate-y-0.5 transition-all">
                    <span class="w-14 h-14 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center text-2xl sm:text-3xl text-brand-600 dark:text-brand-400 mb-2.5">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
                        @else
                            <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i>
                        @endif
                    </span>
                    <span class="text-[12px] sm:text-[13px] font-bold text-slate-800 dark:text-slate-100 group-hover:text-brand-700 dark:group-hover:text-brand-400 leading-tight line-clamp-2">{{ $category->localized_name }}</span>
                    <span class="hidden sm:block text-[11px] text-slate-400 mt-1">{{ $category->sub_categories_count ?? $category->subCategories->count() }} {{ $gu ? 'પેટા શ્રેણી' : 'subcategories' }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Offers -->
    @if($activeOffers->count() > 0)
        <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" aria-labelledby="homeOffers">
            <div class="flex items-end justify-between gap-4 mb-4">
                <h2 id="homeOffers" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'આજની કૂપન ઑફર' : 'Coupons for you' }}</h2>
                <a href="{{ route('pages.offers') }}" class="text-[13px] font-bold text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1 shrink-0">{{ __('messages.offers') }}<i class="ph-bold ph-arrow-right"></i></a>
            </div>
            <div class="fx-rail md:!grid-flow-row md:!grid-cols-3 md:overflow-visible" style="grid-auto-columns: minmax(280px, 90%)">
                @foreach($activeOffers as $offer)
                    <div class="relative overflow-hidden rounded-2xl p-4 sm:p-5 flex items-center gap-4 bg-gradient-to-br {{ ['from-amber-50 to-orange-50 border-amber-200 dark:from-amber-500/10 dark:to-orange-500/5 dark:border-amber-500/20', 'from-emerald-50 to-teal-50 border-emerald-200 dark:from-emerald-500/10 dark:to-teal-500/5 dark:border-emerald-500/20', 'from-violet-50 to-fuchsia-50 border-violet-200 dark:from-violet-500/10 dark:to-fuchsia-500/5 dark:border-violet-500/20'][$loop->index % 3] }} border">
                        <span class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center text-2xl {{ ['text-amber-500', 'text-emerald-500', 'text-violet-500'][$loop->index % 3] }} shrink-0"><i class="ph-duotone ph-ticket"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">
                                {{ $offer->discount_type === 'percentage' ? rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.') . '% ' . __('messages.off') : '₹' . rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.') . ' ' . ($gu ? 'ફ્લેટ' : 'FLAT') }}
                            </p>
                            <p class="text-[12px] font-semibold text-slate-600 dark:text-slate-300 truncate">{{ $offer->localized_title }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $gu ? 'ન્યૂનતમ ઓર્ડર' : 'Min. order' }} ₹{{ number_format($offer->min_order_amount, 0) }}</p>
                        </div>
                        <button type="button" data-copy="{{ $offer->code }}" class="shrink-0 flex flex-col items-center px-3 py-2 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-white/70 dark:bg-slate-900/60 hover:border-brand-500 transition-colors" title="{{ $gu ? 'કોડ કૉપિ કરો' : 'Copy code' }}">
                            <span class="font-mono text-[13px] font-extrabold text-slate-900 dark:text-white">{{ $offer->code }}</span>
                            <span class="text-[10px] font-bold text-brand-700 dark:text-brand-400 inline-flex items-center gap-1"><i class="ph ph-copy"></i><span data-copy-label>{{ $gu ? 'કૉપિ' : 'Copy' }}</span></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Featured products -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" aria-labelledby="homeFeatured">
        <div class="flex items-end justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <h2 id="homeFeatured" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'ખેતરથી તાજું — ખાસ પસંદગી' : 'Farm-fresh featured picks' }}</h2>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $gu ? 'રોજ પસંદ કરેલા ફળો, શાકભાજી અને ડેરી' : 'Hand-picked daily fruits, veggies and dairy' }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-[13px] font-bold text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1 shrink-0">{{ $gu ? 'બધું જુઓ' : 'View all' }}<i class="ph-bold ph-arrow-right"></i></a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
            @foreach($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <!-- Deals -->
    @if($discountedProducts->count() > 0)
        <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" aria-labelledby="homeDeals">
            <div class="rounded-3xl bg-gradient-to-br from-brand-50 to-teal-50 dark:from-brand-500/10 dark:to-teal-500/5 border border-brand-100 dark:border-brand-500/20 p-4 sm:p-6">
                <div class="flex items-end justify-between gap-4 mb-4">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400"><i class="ph-fill ph-fire"></i>{{ $gu ? 'આજના ડીલ' : "Today's deals" }}</span>
                        <h2 id="homeDeals" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'ખાસ ડિસ્કાઉન્ટ અને ઑફર' : 'Special discounts on daily staples' }}</h2>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'price_asc']) }}" class="hidden sm:inline-flex text-[13px] font-bold text-brand-700 dark:text-brand-400 hover:underline items-center gap-1 shrink-0">{{ $gu ? 'બધું જુઓ' : 'View all' }}<i class="ph-bold ph-arrow-right"></i></a>
                </div>
                <div class="fx-rail">
                    @foreach($discountedProducts as $product)
                        @include('frontend.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Recently viewed (filled from this browser's history) -->
    <section class="hidden max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" data-recently-viewed aria-labelledby="homeRecent">
        <div class="flex items-end justify-between gap-4 mb-4">
            <h2 id="homeRecent" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'તાજેતરમાં જોયેલા' : 'Recently viewed' }}</h2>
            <button type="button" data-rv-clear class="text-[12px] font-bold text-slate-500 hover:text-rose-600">{{ $gu ? 'સાફ કરો' : 'Clear' }}</button>
        </div>
        <div class="fx-rail" data-rv-rail></div>
    </section>
</div>
@endsection
