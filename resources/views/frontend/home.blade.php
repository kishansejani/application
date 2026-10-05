@extends('frontend.layouts.app')

@section('title', __('messages.store_name') . ' - ' . __('messages.tagline'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="space-y-10 sm:space-y-14 pb-6">

    <!-- Hero slider -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6">
        @if($sliders->count())
            <div class="relative rounded-3xl overflow-hidden bg-slate-950 shadow-2xl shadow-emerald-950/20 border border-slate-800/80" data-slider aria-roledescription="carousel">
                <div class="fx-slider-track">
                    @foreach($sliders as $slider)
                        <div class="relative min-h-[380px] xs:min-h-[420px] sm:min-h-[460px] lg:min-h-[500px] flex items-center" aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ $sliders->count() }}">
                            <!-- High Res Background Image -->
                            <img src="{{ $slider->image_url }}" alt="{{ $slider->localized_title }}" class="absolute inset-0 w-full h-full object-cover object-center transform scale-100 hover:scale-105 transition-transform duration-1000" @if(!$loop->first) loading="lazy" @endif>
                            
                            <!-- Deep Gradient Layer with Vibrant Accent Glow -->
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-950/75 to-slate-950/20 sm:to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent sm:hidden"></div>

                            <!-- Content Overlay -->
                            <div class="relative w-full max-w-7xl mx-auto px-5 sm:px-10 lg:px-14 py-8 sm:py-12 flex flex-col justify-center">
                                <div class="max-w-2xl space-y-3.5 sm:space-y-5 text-white">
                                    <!-- Badge Pill -->
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($slider->localized_badge)
                                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-extrabold text-[11px] sm:text-[12px] uppercase tracking-wider rounded-full shadow-lg shadow-amber-500/20 animate-pulse">
                                                <i class="ph-fill ph-lightning"></i>{{ $slider->localized_badge }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-bold text-[11px] sm:text-[12px] rounded-full">
                                            <i class="ph-fill ph-plant text-emerald-400"></i>{{ $gu ? '૧૦૦% શુદ્ધ ખેત ઉત્પાદન' : '100% Organic & Farm Fresh' }}
                                        </span>
                                    </div>

                                    <!-- Main Title with Elegant Typography -->
                                    <h2 class="text-2xl xs:text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.12] drop-shadow-md text-white">
                                        {{ $slider->localized_title }}
                                    </h2>

                                    <!-- Subtitle -->
                                    @if($slider->localized_subtitle)
                                        <p class="text-[13.5px] sm:text-lg text-slate-200/90 leading-relaxed font-medium max-w-xl drop-shadow">
                                            {{ $slider->localized_subtitle }}
                                        </p>
                                    @endif

                                    <!-- Action Buttons & Quick Stats -->
                                    <div class="pt-2 sm:pt-3 flex flex-wrap items-center gap-3">
                                        <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary !font-extrabold !px-7 !py-3.5 !rounded-2xl !text-sm sm:!text-base shadow-xl shadow-brand-600/30 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                            <span>{{ __('messages.start_shopping') }}</span><i class="ph-bold ph-arrow-right"></i>
                                        </a>

                                        <a href="{{ route('pages.offers') }}" class="fx-btn !bg-white/15 hover:!bg-white/25 !text-white backdrop-blur-md border border-white/30 !font-bold !px-6 !py-3.5 !rounded-2xl !text-sm sm:!text-base hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                            <i class="ph-fill ph-seal-percent text-amber-400"></i><span>{{ __('messages.offers_and_deals') }}</span>
                                        </a>
                                    </div>

                                    <!-- Floating Rating / Trust Note -->
                                    <div class="pt-2 flex items-center gap-4 text-xs font-semibold text-slate-300">
                                        <div class="flex items-center gap-1 text-amber-400">
                                            <i class="ph-fill ph-star"></i>
                                            <i class="ph-fill ph-star"></i>
                                            <i class="ph-fill ph-star"></i>
                                            <i class="ph-fill ph-star"></i>
                                            <i class="ph-fill ph-star"></i>
                                            <span class="text-white font-bold ml-1">4.9 / 5.0</span>
                                        </div>
                                        <span class="w-1 h-1 bg-white/40 rounded-full"></span>
                                        <span class="text-slate-200">{{ $gu ? '૫,૦૦૦+ સંતુષ્ટ ગ્રાહકો' : '5,000+ Happy Households' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Custom Slider Controls -->
                @if($sliders->count() > 1)
                    <div class="absolute bottom-4 sm:bottom-6 left-5 sm:left-10 lg:left-14 flex items-center gap-2 z-10">
                        @foreach($sliders as $slider)
                            <button type="button" data-dot class="fx-dot {{ $loop->first ? 'is-active' : '' }} !h-2.5 !w-7 !rounded-full !bg-white/40 [&.is-active]:!bg-brand-500 [&.is-active]:!w-10 transition-all" aria-label="{{ __('messages.slide', ['n' => $loop->iteration]) }}"></button>
                        @endforeach
                    </div>
                    <div class="hidden sm:flex absolute bottom-5 right-6 gap-2.5 z-10">
                        <button type="button" data-prev class="w-11 h-11 rounded-2xl bg-slate-900/60 hover:bg-brand-600 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all shadow-lg hover:scale-105" aria-label="{{ __('messages.prev_slide') }}"><i class="ph-bold ph-caret-left text-lg"></i></button>
                        <button type="button" data-next class="w-11 h-11 rounded-2xl bg-slate-900/60 hover:bg-brand-600 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all shadow-lg hover:scale-105" aria-label="{{ __('messages.next_slide') }}"><i class="ph-bold ph-caret-right text-lg"></i></button>
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-8 sm:p-14 shadow-2xl">
                <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight max-w-xl">{{ __('messages.tagline') }}</h2>
                <a href="{{ route('products.index') }}" class="mt-6 fx-btn bg-white text-brand-800">{{ __('messages.start_shopping') }}<i class="ph-bold ph-arrow-right"></i></a>
            </div>
        @endif
    </section>

    <!-- Super Premium Trust & Delivery Feature Strip -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 -mt-2 sm:-mt-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Express 2-Hour -->
            <div class="group relative overflow-hidden rounded-2xl p-4 sm:p-5 border border-emerald-200/80 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-white dark:from-emerald-950/40 dark:via-emerald-950/20 dark:to-slate-900 shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-lightning"></i>
                    </span>
                    <div class="min-w-0">
                        <h4 class="text-sm sm:text-[15px] font-extrabold text-slate-900 dark:text-white leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $gu ? '૨ કલાક સુપરફાસ્ટ' : '2-Hour Delivery' }}</h4>
                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ $gu ? 'બપોરે ૧૨ પહેલાં ઓર્ડર' : 'Orders before 12 PM' }}</p>
                    </div>
                </div>
            </div>

            <!-- 2. Farm Fresh -->
            <div class="group relative overflow-hidden rounded-2xl p-4 sm:p-5 border border-teal-200/80 dark:border-teal-500/30 bg-gradient-to-br from-teal-500/10 via-teal-500/5 to-white dark:from-teal-950/40 dark:via-teal-950/20 dark:to-slate-900 shadow-sm hover:shadow-xl hover:shadow-teal-500/10 hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-700 text-white flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-teal-600/30 group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-plant"></i>
                    </span>
                    <div class="min-w-0">
                        <h4 class="text-sm sm:text-[15px] font-extrabold text-slate-900 dark:text-white leading-tight group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">{{ $gu ? '૧૦૦% ખેતરથી તાજું' : '100% Farm Fresh' }}</h4>
                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ $gu ? 'સ્થાનિક ખેડૂતો પાસેથી' : 'Direct from local farms' }}</p>
                    </div>
                </div>
            </div>

            <!-- 3. Free Delivery -->
            <div class="group relative overflow-hidden rounded-2xl p-4 sm:p-5 border border-amber-200/80 dark:border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-white dark:from-amber-950/40 dark:via-amber-950/20 dark:to-slate-900 shadow-sm hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-truck"></i>
                    </span>
                    <div class="min-w-0">
                        <h4 class="text-sm sm:text-[15px] font-extrabold text-slate-900 dark:text-white leading-tight group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $gu ? 'મફત ડિલિવરી' : 'Free Delivery' }}</h4>
                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ $gu ? '₹૧૯૯ થી વધુના ઓર્ડર પર' : 'On orders above ₹199' }}</p>
                    </div>
                </div>
            </div>

            <!-- 4. Quality Guarantee -->
            <div class="group relative overflow-hidden rounded-2xl p-4 sm:p-5 border border-sky-200/80 dark:border-sky-500/30 bg-gradient-to-br from-sky-500/10 via-sky-500/5 to-white dark:from-sky-950/40 dark:via-sky-950/20 dark:to-slate-900 shadow-sm hover:shadow-xl hover:shadow-sky-500/10 hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-700 text-white flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-sky-600/30 group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-shield-check"></i>
                    </span>
                    <div class="min-w-0">
                        <h4 class="text-sm sm:text-[15px] font-extrabold text-slate-900 dark:text-white leading-tight group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ $gu ? 'સરળ રિટર્ન ગેરંટી' : 'Quality Guarantee' }}</h4>
                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ $gu ? 'દરવાજે જ તાત્કાલિક બદલી' : 'Instant doorstep exchange' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section (Ultra-Premium Card Showcase) -->
    <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" aria-labelledby="homeCats">
        <div class="flex items-end justify-between gap-4 mb-4 sm:mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">{{ $gu ? 'શ્રેણીઓ' : 'Categories' }}</span>
                </div>
                <h2 id="homeCats" class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'કેટેગરી પ્રમાણે ખરીદી કરો' : 'Shop by Category' }}</h2>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $gu ? 'તાજી ખેત પેદાશો, ડેરી અને ઘરની રોજિંદી જરૂરિયાતો' : 'Farm-fresh produce, dairy and household essentials' }}</p>
            </div>
            <a href="{{ route('categories.index') }}" class="group text-[13px] font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-600 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 hover:shadow-sm transition-all shrink-0">
                <span>{{ $gu ? 'બધી કેટેગરીઝ' : 'View All' }}</span>
                <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 xs:grid-cols-3 sm:grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4">
            @php
                $catThemes = [
                    ['bg' => 'from-emerald-50/90 to-teal-50/30 dark:from-emerald-950/30 dark:to-slate-900', 'border' => 'border-emerald-200/70 dark:border-emerald-800/40 hover:border-emerald-500 hover:shadow-emerald-500/15', 'accent' => 'text-emerald-600 dark:text-emerald-400'],
                    ['bg' => 'from-amber-50/90 to-orange-50/30 dark:from-amber-950/30 dark:to-slate-900', 'border' => 'border-amber-200/70 dark:border-amber-800/40 hover:border-amber-500 hover:shadow-amber-500/15', 'accent' => 'text-amber-600 dark:text-amber-400'],
                    ['bg' => 'from-orange-50/90 to-yellow-50/30 dark:from-orange-950/30 dark:to-slate-900', 'border' => 'border-orange-200/70 dark:border-orange-800/40 hover:border-orange-500 hover:shadow-orange-500/15', 'accent' => 'text-orange-600 dark:text-orange-400'],
                    ['bg' => 'from-rose-50/90 to-red-50/30 dark:from-rose-950/30 dark:to-slate-900', 'border' => 'border-rose-200/70 dark:border-rose-800/40 hover:border-rose-500 hover:shadow-rose-500/15', 'accent' => 'text-rose-600 dark:text-rose-400'],
                    ['bg' => 'from-yellow-50/90 to-amber-50/30 dark:from-yellow-950/30 dark:to-slate-900', 'border' => 'border-yellow-200/70 dark:border-yellow-800/40 hover:border-yellow-500 hover:shadow-yellow-500/15', 'accent' => 'text-yellow-600 dark:text-yellow-400'],
                    ['bg' => 'from-sky-50/90 to-blue-50/30 dark:from-sky-950/30 dark:to-slate-900', 'border' => 'border-sky-200/70 dark:border-sky-800/40 hover:border-sky-500 hover:shadow-sky-500/15', 'accent' => 'text-sky-600 dark:text-sky-400'],
                ];
            @endphp
            @foreach($categories as $category)
                @php $th = $catThemes[$loop->index % count($catThemes)]; @endphp
                <a href="{{ route('categories.show', $category->slug) }}" class="group relative flex flex-col items-center justify-between text-center p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border bg-gradient-to-b {{ $th['bg'] }} {{ $th['border'] }} shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 min-h-[195px] sm:min-h-[220px]">
                    <!-- Circular Image Showcase with Luxury Ring -->
                    <div class="w-20 h-20 sm:w-24 sm:h-24 md:w-26 md:h-26 rounded-2xl sm:rounded-3xl overflow-hidden shadow-md ring-4 ring-white/95 dark:ring-slate-800/90 bg-white dark:bg-slate-800 flex items-center justify-center shrink-0 mb-2.5 group-hover:scale-105 group-hover:rotate-1 transition-all duration-300">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="{{ $category->localized_name }}" class="w-full h-full object-cover object-center" loading="lazy">
                        @else
                            <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }} text-3xl text-emerald-600 dark:text-emerald-400"></i>
                        @endif
                    </div>

                    <!-- Category Name -->
                    <div class="flex-1 flex items-center justify-center px-1">
                        <h3 class="text-[13px] sm:text-[14px] font-extrabold text-slate-800 dark:text-slate-100 group-hover:{{ $th['accent'] }} leading-tight line-clamp-2 transition-colors">
                            {{ $category->localized_name }}
                        </h3>
                    </div>

                    <!-- Subcategory Count Pill -->
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 mt-1.5 rounded-full text-[10.5px] font-bold text-slate-500 dark:text-slate-400 bg-white/90 dark:bg-slate-800/90 border border-slate-200/60 dark:border-slate-700/60 shadow-xs">
                        {{ $category->sub_categories_count ?? $category->subCategories->count() }} {{ $gu ? 'પેટા શ્રેણી' : 'items' }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Offers / Coupons Section (Authentic Voucher Ticket Cards) -->
    @if($activeOffers->count() > 0)
        <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" aria-labelledby="homeOffers">
            <div class="flex items-end justify-between gap-4 mb-4 sm:mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">{{ $gu ? 'વિશેષ બચત' : 'Special Savings' }}</span>
                    </div>
                    <h2 id="homeOffers" class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'આજની કૂપન ઑફર્સ' : 'Coupons & Deals for You' }}</h2>
                </div>
                <a href="{{ route('pages.offers') }}" class="group text-[13px] font-bold text-amber-700 dark:text-amber-400 hover:text-amber-600 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40 hover:shadow-sm transition-all shrink-0">
                    <span>{{ __('messages.offers_and_deals') }}</span>
                    <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $offerThemes = [
                        ['bg' => 'from-amber-50 via-orange-50/60 to-amber-100/40 dark:from-amber-950/30 dark:via-orange-950/20 dark:to-slate-900', 'border' => 'border-amber-300/80 dark:border-amber-500/30', 'iconBg' => 'bg-amber-500 text-white', 'codeColor' => 'text-amber-700 dark:text-amber-400', 'btnBg' => 'bg-amber-500 hover:bg-amber-600 text-white'],
                        ['bg' => 'from-emerald-50 via-teal-50/60 to-emerald-100/40 dark:from-emerald-950/30 dark:via-teal-950/20 dark:to-slate-900', 'border' => 'border-emerald-300/80 dark:border-emerald-500/30', 'iconBg' => 'bg-emerald-500 text-white', 'codeColor' => 'text-emerald-700 dark:text-emerald-400', 'btnBg' => 'bg-emerald-600 hover:bg-emerald-700 text-white'],
                        ['bg' => 'from-violet-50 via-purple-50/60 to-fuchsia-100/40 dark:from-violet-950/30 dark:via-purple-950/20 dark:to-slate-900', 'border' => 'border-violet-300/80 dark:border-violet-500/30', 'iconBg' => 'bg-violet-600 text-white', 'codeColor' => 'text-violet-700 dark:text-violet-400', 'btnBg' => 'bg-violet-600 hover:bg-violet-700 text-white'],
                    ];
                @endphp
                @foreach($activeOffers as $offer)
                    @php $oth = $offerThemes[$loop->index % count($offerThemes)]; @endphp
                    <div class="relative overflow-hidden rounded-3xl p-5 border {{ $oth['border'] }} bg-gradient-to-br {{ $oth['bg'] }} shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <!-- Top Header with Discount Value -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <span class="w-12 h-12 rounded-2xl {{ $oth['iconBg'] }} shadow-md flex items-center justify-center text-2xl shrink-0">
                                    <i class="ph-duotone ph-ticket"></i>
                                </span>
                                <div>
                                    <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white leading-none tracking-tight">
                                        {{ $offer->discount_type === 'percentage' ? rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.') . '% ' . __('messages.off') : '₹' . rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.') . ' ' . ($gu ? 'ફ્લેટ' : 'FLAT') }}
                                    </span>
                                    <p class="text-[12.5px] font-bold text-slate-700 dark:text-slate-200 mt-1 line-clamp-1">{{ $offer->localized_title }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer with Min Order and 1-Click Code Box -->
                        <div class="pt-3 mt-2 border-t border-dashed border-slate-300/80 dark:border-slate-700/80 flex items-center justify-between gap-2">
                            <span class="text-[11.5px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ $gu ? 'ન્યૂનતમ ઓર્ડર' : 'Min. order' }} <strong class="text-slate-800 dark:text-slate-100">₹{{ number_format($offer->min_order_amount, 0) }}</strong>
                            </span>

                            <button type="button" data-copy="{{ $offer->code }}" class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 border-dashed border-slate-400/80 dark:border-slate-500 bg-white/90 dark:bg-slate-900/90 hover:border-emerald-500 shadow-xs transition-all cursor-pointer group" title="{{ $gu ? 'કોડ કૉપિ કરો' : 'Copy code' }}">
                                <span class="font-mono text-[13px] font-black tracking-wider text-slate-900 dark:text-white">{{ $offer->code }}</span>
                                <span class="text-[10.5px] font-bold text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1"><i class="ph ph-copy text-xs"></i><span data-copy-label>{{ $gu ? 'કૉપિ' : 'Copy' }}</span></span>
                            </button>
                        </div>
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
