@extends('frontend.layouts.app')

@section('title', $subcategory->localized_name . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Subcategory Header -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl">
        <div class="flex items-center gap-4 sm:gap-6">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur overflow-hidden flex items-center justify-center text-3xl shrink-0 border border-white/20">
                <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->localized_name }}" class="w-full h-full object-cover">
            </div>
            <div>
                <a href="{{ route('categories.show', $subcategory->category->slug) }}" class="text-xs text-brand-400 font-bold uppercase hover:underline">
                    &larr; {{ $subcategory->category->localized_name }}
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">{{ $subcategory->localized_name }}</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">{{ $subcategory->localized_description }}</p>
            </div>
        </div>

        <div class="bg-white/10 px-4 py-2.5 rounded-2xl backdrop-blur text-xs font-semibold shrink-0 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-400"></i>
            <span>2-Hour Delivery: <strong>{{ app()->getLocale() === 'gu' ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</strong></span>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        @forelse($subcategory->products as $product)
            <div class="group bg-white rounded-3xl border border-slate-200 hover:border-brand-400 p-4 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between relative">
                @if($product->has_discount)
                    <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-emerald-600 text-white font-extrabold text-[10px] shadow z-10">
                        {{ $product->discount_percent }}% OFF
                    </span>
                @endif

                <button onclick="toggleWishlist({{ $product->id }}, this)" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur border border-slate-200 text-slate-400 hover:text-rose-500 flex items-center justify-center text-xs shadow-sm z-10 transition-colors" title="{{ __('messages.wishlist') }}">
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
                    <a href="{{ route('products.show', $product->slug) }}" class="block font-bold text-slate-900 text-xs sm:text-sm hover:text-brand-600 line-clamp-1">
                        {{ $product->localized_name }}
                    </a>
                    <span class="text-[11px] text-slate-500 font-semibold">{{ $product->unit }}</span>
                </div>

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
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200">
                <i class="fa-solid fa-basket-shopping text-5xl text-slate-300 mb-3"></i>
                <h4 class="font-bold text-slate-700 text-base">No products found in this subcategory yet.</h4>
            </div>
        @endforelse
    </div>

</div>
@endsection
