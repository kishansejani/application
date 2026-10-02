@extends('frontend.layouts.app')

@section('title', __('messages.store_name') . ' - ' . __('messages.tagline'))

@section('content')
<div class="space-y-12 pb-16">

    <!-- Hero Swiper Slider Banner -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="swiper heroSwiper rounded-3xl overflow-hidden shadow-xl border border-slate-200">
            <div class="swiper-wrapper">
                @foreach($sliders as $slider)
                    <div class="swiper-slide relative h-72 sm:h-96 md:h-[420px] bg-slate-900">
                        <img src="{{ $slider->image_url }}" alt="{{ $slider->localized_title }}" class="w-full h-full object-cover opacity-85">
                        
                        <!-- Gradient Overlay & Bilingual Copy -->
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/50 to-transparent flex items-center">
                            <div class="max-w-xl p-6 sm:p-12 space-y-4 text-white">
                                @if($slider->localized_badge)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500 text-slate-950 font-extrabold text-xs uppercase tracking-wider rounded-full shadow">
                                        <i class="fa-solid fa-bolt"></i>
                                        <span>{{ $slider->localized_badge }}</span>
                                    </span>
                                @endif

                                <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">
                                    {{ $slider->localized_title }}
                                </h2>

                                <p class="text-xs sm:text-sm text-slate-200 font-medium">
                                    {{ $slider->localized_subtitle }}
                                </p>

                                <div class="pt-2">
                                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-bold shadow-lg shadow-brand-600/30 transition-all hover:scale-105">
                                        <span>{{ __('messages.start_shopping') }}</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next !text-white after:!text-lg !w-10 !h-10 rounded-full !bg-black/30 backdrop-blur"></div>
            <div class="swiper-button-prev !text-white after:!text-lg !w-10 !h-10 rounded-full !bg-black/30 backdrop-blur"></div>
        </div>
    </div>

    <!-- 2-Hour Express Delivery Feature Strip -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-emerald-600 via-brand-600 to-teal-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-700/15 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center text-3xl sm:text-4xl shadow-inner shrink-0">
                    ⚡
                </div>
                <div>
                    <span class="inline-block px-3 py-0.5 rounded-full bg-amber-400 text-slate-950 font-extrabold text-[10px] uppercase tracking-wider mb-1">
                        {{ __('messages.delivery_promise_title') }}
                    </span>
                    <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                        {{ __('messages.delivery_promise_desc') }}
                    </h3>
                    <p class="text-xs sm:text-sm text-emerald-100 font-medium mt-1">
                        Current Delivery Window: <strong class="text-white underline">{{ app()->getLocale() === 'gu' ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</strong>
                    </p>
                </div>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <a href="{{ route('products.index') }}" class="px-6 py-3.5 bg-white text-brand-900 hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-lg transition-all">
                    Order for Quick Delivery &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Circular Categories Navigation Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.categories') }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Explore by fresh farm produce & household essentials</p>
            </div>
            <a href="{{ route('categories.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800 flex items-center gap-1">
                <span>{{ __('messages.all_categories') }}</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="group bg-white p-4 rounded-3xl border border-slate-200 hover:border-brand-500 shadow-sm hover:shadow-md transition-all flex flex-col items-center text-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 mb-3 group-hover:scale-105 transition-transform">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="{{ $category->localized_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl text-brand-600 bg-brand-50">
                                <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-brand-600 transition-colors line-clamp-1">
                        {{ $category->localized_name }}
                    </h4>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">
                        {{ $category->sub_categories_count ?? $category->subCategories->count() }} Subs
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Active Promo Offers Strip -->
    @if($activeOffers->count() > 0)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($activeOffers as $offer)
                    <div class="p-5 rounded-3xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50/40 flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="px-2.5 py-0.5 bg-amber-500 text-slate-950 font-extrabold text-[10px] rounded-md uppercase">
                                {{ $offer->discount_type === 'percentage' ? $offer->discount_value . '% OFF' : '₹' . $offer->discount_value . ' FLAT' }}
                            </span>
                            <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $offer->localized_title }}</h4>
                            <p class="text-[11px] text-slate-500 font-medium">Min Order: ₹{{ number_format($offer->min_order_amount, 2) }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <button onclick="navigator.clipboard.writeText('{{ $offer->code }}'); toastr.success('Code {{ $offer->code }} copied!')" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-mono font-bold transition-all shadow">
                                {{ $offer->code }} <i class="fa-regular fa-copy ml-1"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Featured Products Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Farm Fresh Featured Products</h3>
                <p class="text-xs text-slate-500 mt-0.5">Top-picked daily fruits, veggies & organic dairy</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800 flex items-center gap-1">
                <span>View All &rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($featuredProducts as $product)
                <div class="group bg-white rounded-3xl border border-slate-200 hover:border-brand-400 p-4 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between relative">
                    <!-- Discount Pill -->
                    @if($product->has_discount)
                        <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-emerald-600 text-white font-extrabold text-[10px] shadow z-10">
                            {{ $product->discount_percent }}% OFF
                        </span>
                    @endif

                    <!-- Wishlist Button -->
                    <button onclick="toggleWishlist({{ $product->id }}, this)" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur border border-slate-200 text-slate-400 hover:text-rose-500 flex items-center justify-center text-xs shadow-sm z-10 transition-colors" title="{{ __('messages.wishlist') }}">
                        <i class="fa-solid fa-heart {{ Auth::check() && Auth::user()->wishlists()->where('product_id', $product->id)->exists() ? 'heart-active text-rose-600' : '' }}"></i>
                    </button>

                    <!-- Product Image & Quick View trigger -->
                    <div class="relative w-full h-40 sm:h-48 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 mb-3">
                        <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </a>
                        <button onclick="openQuickView({{ $product->id }})" class="absolute bottom-2 inset-x-2 py-1.5 bg-slate-900/80 hover:bg-slate-900 text-white text-[11px] font-bold rounded-xl opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur">
                            {{ __('messages.quick_view') }}
                        </button>
                    </div>

                    <!-- Details -->
                    <div class="space-y-1 mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            {{ $product->category ? $product->category->localized_name : '' }}
                        </span>
                        <a href="{{ route('products.show', $product->slug) }}" class="block font-bold text-slate-900 text-xs sm:text-sm hover:text-brand-600 line-clamp-1">
                            {{ $product->localized_name }}
                        </a>
                        <span class="text-[11px] text-slate-500 font-semibold">{{ $product->unit }}</span>
                    </div>

                    <!-- Price & Add Button -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <div>
                            <span class="font-extrabold text-slate-900 text-sm sm:text-base">₹{{ number_format($product->effective_price, 2) }}</span>
                            @if($product->has_discount)
                                <span class="block text-[11px] text-slate-400 line-through">₹{{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>

                        @if($product->is_in_stock)
                            <button onclick="addToCart({{ $product->id }})" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 transition-all flex items-center gap-1.5">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>{{ __('messages.add_to_cart') }}</span>
                            </button>
                        @else
                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-400 text-[10px] font-bold">
                                Out of Stock
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Daily Discount & Grocery Deals Grid -->
    @if($discountedProducts->count() > 0)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Today's Special Discounts & Offers</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Save big on daily staples and kitchen essentials</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($discountedProducts as $product)
                    <div class="group bg-white rounded-3xl border border-slate-200 hover:border-brand-400 p-4 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between relative">
                        <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-emerald-600 text-white font-extrabold text-[10px] shadow z-10">
                            {{ $product->discount_percent }}% OFF
                        </span>

                        <button onclick="toggleWishlist({{ $product->id }}, this)" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur border border-slate-200 text-slate-400 hover:text-rose-500 flex items-center justify-center text-xs shadow-sm z-10 transition-colors">
                            <i class="fa-solid fa-heart {{ Auth::check() && Auth::user()->wishlists()->where('product_id', $product->id)->exists() ? 'heart-active text-rose-600' : '' }}"></i>
                        </button>

                        <div class="relative w-full h-40 sm:h-48 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 mb-3">
                            <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                            <button onclick="openQuickView({{ $product->id }})" class="absolute bottom-2 inset-x-2 py-1.5 bg-slate-900/80 hover:bg-slate-900 text-white text-[11px] font-bold rounded-xl opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur">
                                {{ __('messages.quick_view') }}
                            </button>
                        </div>

                        <div class="space-y-1 mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                {{ $product->category ? $product->category->localized_name : '' }}
                            </span>
                            <a href="{{ route('products.show', $product->slug) }}" class="block font-bold text-slate-900 text-xs sm:text-sm hover:text-brand-600 line-clamp-1">
                                {{ $product->localized_name }}
                            </a>
                            <span class="text-[11px] text-slate-500 font-semibold">{{ $product->unit }}</span>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                            <div>
                                <span class="font-extrabold text-slate-900 text-sm sm:text-base">₹{{ number_format($product->effective_price, 2) }}</span>
                                <span class="block text-[11px] text-slate-400 line-through">₹{{ number_format($product->price, 2) }}</span>
                            </div>

                            <button onclick="addToCart({{ $product->id }})" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 transition-all flex items-center gap-1.5">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>{{ __('messages.add_to_cart') }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Trust Badges 4-Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-8 bg-white rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-900 text-xs">100% Farm Fresh</h5>
                    <p class="text-[11px] text-slate-400">Directly sourced from trusted farmers</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-900 text-xs">2-Hour Express Delivery</h5>
                    <p class="text-[11px] text-slate-400">Order before 12:00 PM cutoff</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-900 text-xs">Best Wholesale Prices</h5>
                    <p class="text-[11px] text-slate-400">Save up to 30% on daily essentials</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-900 text-xs">No-Questions Return</h5>
                    <p class="text-[11px] text-slate-400">Instant replacement at your doorstep</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    new Swiper('.heroSwiper', {
        loop: true,
        autoplay: {
            delay: 4500,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>
@endpush
