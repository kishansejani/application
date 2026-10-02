@extends('frontend.layouts.app')

@section('title', $product->localized_name . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('home') }}" class="hover:text-slate-700">{{ __('messages.home') }}</a>
        <span>/</span>
        <a href="{{ route('categories.show', $product->category->slug) }}" class="hover:text-slate-700">{{ $product->category->localized_name }}</a>
        @if($product->subCategory)
            <span>/</span>
            <a href="{{ route('subcategories.show', $product->subCategory->slug) }}" class="hover:text-slate-700">{{ $product->subCategory->localized_name }}</a>
        @endif
        <span>/</span>
        <span class="text-slate-900 font-bold truncate max-w-xs">{{ $product->localized_name }}</span>
    </nav>

    <!-- Main Product Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

            <!-- Left: Image Gallery -->
            <div class="space-y-4">
                <div class="relative w-full h-80 sm:h-96 rounded-3xl overflow-hidden border border-slate-200 bg-slate-50 shadow-inner">
                    <img id="mainProductImage" src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" class="w-full h-full object-cover">
                    @if($product->has_discount)
                        <span class="absolute top-4 left-4 px-3 py-1 bg-emerald-600 text-white font-extrabold text-xs rounded-full shadow">
                            {{ $product->discount_percent }}% OFF
                        </span>
                    @endif
                </div>

                <!-- Gallery Thumbnails -->
                @if($product->images->count() > 0)
                    <div class="flex items-center gap-3 overflow-x-auto pb-2">
                        <div onclick="swapMainImage('{{ $product->thumbnail_url }}')" class="w-16 h-16 rounded-xl overflow-hidden border-2 border-brand-600 cursor-pointer shrink-0">
                            <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
                        </div>
                        @foreach($product->images as $img)
                            <div onclick="swapMainImage('{{ $img->image_url }}')" class="w-16 h-16 rounded-xl overflow-hidden border border-slate-200 hover:border-brand-600 cursor-pointer shrink-0 transition-colors">
                                <img src="{{ $img->image_url }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Product Details & Purchase Box -->
            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 bg-brand-50 text-brand-700 font-bold text-xs uppercase tracking-wider rounded-lg">
                            {{ $product->category->localized_name }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">{{ $product->sku }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        {{ $product->localized_name }}
                    </h1>

                    <p class="text-xs font-semibold text-slate-500">
                        {{ __('messages.unit') }}: <strong class="text-slate-800">{{ $product->unit }}</strong>
                    </p>

                    <!-- Pricing Display -->
                    <div class="flex items-baseline gap-3 pt-2">
                        <span class="text-3xl font-extrabold text-slate-900">
                            ₹{{ number_format($product->effective_price, 2) }}
                        </span>
                        @if($product->has_discount)
                            <span class="text-base text-slate-400 line-through">
                                ₹{{ number_format($product->price, 2) }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-extrabold text-xs">
                                {{ __('messages.save') }} ₹{{ number_format($product->price - $product->discount_price, 2) }} ({{ $product->discount_percent }}% {{ __('messages.off') }})
                            </span>
                        @endif
                    </div>

                    <!-- Short Description -->
                    @if($product->localized_short_description)
                        <p class="text-xs text-slate-600 leading-relaxed pt-2">
                            {{ $product->localized_short_description }}
                        </p>
                    @endif

                    <!-- 2-Hour Express Delivery Box -->
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50/50 border border-emerald-200 text-emerald-900 text-xs space-y-1.5 my-4">
                        <div class="flex items-center gap-2 font-extrabold text-sm text-emerald-800">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            <span>{{ __('messages.delivery_promise_title') }}</span>
                        </div>
                        <p class="text-[11px] text-emerald-700">
                            {{ __('messages.delivery_promise_desc') }}
                        </p>
                        <div class="text-xs font-bold pt-1 text-slate-900">
                            {{ __('messages.est_delivery') }}: <span class="text-brand-700 underline">{{ app()->getLocale() === 'gu' ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</span>
                        </div>
                    </div>

                    <!-- Stock indicator -->
                    <div class="text-xs font-bold">
                        @if($product->stock_quantity <= 0)
                            <span class="text-rose-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-xmark"></i> {{ __('messages.out_of_stock') }}
                            </span>
                        @elseif($product->is_low_stock)
                            <span class="text-amber-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation"></i> {{ __('messages.low_stock', ['count' => $product->stock_quantity]) }}
                            </span>
                        @else
                            <span class="text-emerald-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i> {{ __('messages.in_stock') }} ({{ $product->stock_quantity }} available)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Purchase Controls -->
                <div class="pt-6 border-t border-slate-100 flex items-center gap-4">
                    @if($product->is_in_stock)
                        <div class="flex items-center border border-slate-200 rounded-2xl p-1 bg-slate-50">
                            <button type="button" onclick="adjustQty(-1)" class="w-9 h-9 rounded-xl bg-white text-slate-700 font-bold hover:bg-slate-200 transition-colors flex items-center justify-center">-</button>
                            <input type="number" id="detailQtyInput" value="1" min="1" max="{{ $product->stock_quantity }}" class="w-12 bg-transparent text-center font-bold text-sm focus:outline-none">
                            <button type="button" onclick="adjustQty(1)" class="w-9 h-9 rounded-xl bg-white text-slate-700 font-bold hover:bg-slate-200 transition-colors flex items-center justify-center">+</button>
                        </div>

                        <button onclick="addToCart({{ $product->id }}, parseInt(document.getElementById('detailQtyInput').value))" class="flex-1 py-3.5 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-cart-plus"></i>
                            <span>{{ __('messages.add_to_cart') }}</span>
                        </button>
                    @else
                        <button disabled class="flex-1 py-3.5 bg-slate-200 text-slate-400 rounded-2xl text-xs font-bold cursor-not-allowed">
                            {{ __('messages.out_of_stock') }}
                        </button>
                    @endif

                    <button onclick="toggleWishlist({{ $product->id }}, this)" class="p-3.5 rounded-2xl border border-slate-200 hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors" title="{{ __('messages.wishlist') }}">
                        <i class="fa-solid fa-heart text-base {{ Auth::check() && Auth::user()->wishlists()->where('product_id', $product->id)->exists() ? 'heart-active text-rose-600' : '' }}"></i>
                    </button>
                </div>
            </div>

        </div>

        <!-- Full Detailed Description & Nutritional/Usage notes -->
        @if($product->localized_description)
            <div class="mt-12 pt-8 border-t border-slate-100">
                <h3 class="text-lg font-extrabold text-slate-900 mb-4">{{ __('messages.view_details') }}</h3>
                <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3 prose prose-sm max-w-none">
                    {!! nl2br(e($product->localized_description)) !!}
                </div>
            </div>
        @endif
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="space-y-6">
            <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.related_products') }}</h3>

            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedProducts as $rel)
                    <div class="group bg-white rounded-3xl border border-slate-200 hover:border-brand-400 p-4 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between relative">
                        @if($rel->has_discount)
                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-emerald-600 text-white font-extrabold text-[10px] shadow z-10">
                                {{ $rel->discount_percent }}% OFF
                            </span>
                        @endif

                        <div class="relative w-full h-36 rounded-2xl overflow-hidden bg-slate-50 mb-3">
                            <a href="{{ route('products.show', $rel->slug) }}" class="block w-full h-full">
                                <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->localized_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                        </div>

                        <div class="space-y-1 mb-2">
                            <a href="{{ route('products.show', $rel->slug) }}" class="block font-bold text-slate-900 text-xs hover:text-brand-600 line-clamp-1">
                                {{ $rel->localized_name }}
                            </a>
                            <span class="text-[10px] text-slate-500 font-semibold">{{ $rel->unit }}</span>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                            <span class="font-extrabold text-slate-900 text-sm">₹{{ number_format($rel->effective_price, 2) }}</span>
                            <button onclick="addToCart({{ $rel->id }})" class="p-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition-all">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function swapMainImage(url) {
        $('#mainProductImage').attr('src', url);
    }

    function adjustQty(delta) {
        const input = document.getElementById('detailQtyInput');
        let val = parseInt(input.value) || 1;
        val = Math.max(1, Math.min({{ $product->stock_quantity }}, val + delta));
        input.value = val;
    }
</script>
@endpush
