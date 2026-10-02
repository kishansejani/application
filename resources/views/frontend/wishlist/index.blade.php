@extends('frontend.layouts.app')

@section('title', __('messages.wishlist') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.wishlist') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Your saved grocery items for quick re-ordering</p>
        </div>
        <span class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full font-bold text-xs">
            {{ $wishlists->count() }} items saved
        </span>
    </div>

    @if($wishlists->isEmpty())
        <div class="py-20 text-center bg-white rounded-3xl border border-slate-200 shadow-sm p-8 max-w-lg mx-auto space-y-4">
            <div class="w-20 h-20 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-4xl mx-auto shadow-inner">
                <i class="fa-solid fa-heart"></i>
            </div>
            <h3 class="text-xl font-extrabold text-slate-900">Your wishlist is empty</h3>
            <p class="text-xs text-slate-400">Save items you love by tapping the heart icon on any product card.</p>
            <div class="pt-2">
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-bold shadow-lg shadow-brand-500/25 transition-all">
                    <span>Explore Products</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($wishlists as $wish)
                @php $product = $wish->product; @endphp
                @if($product)
                    <div class="group bg-white rounded-3xl border border-slate-200 hover:border-brand-400 p-4 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between relative">
                        <button onclick="toggleWishlist({{ $product->id }}, this); $(this).closest('.group').remove();" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur border border-slate-200 text-rose-600 flex items-center justify-center text-xs shadow-sm z-10 hover:bg-rose-50" title="Remove from wishlist">
                            <i class="fa-solid fa-heart heart-active"></i>
                        </button>

                        <div class="relative w-full h-40 sm:h-48 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 mb-3">
                            <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
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
                            <span class="font-extrabold text-slate-900 text-sm sm:text-base">₹{{ number_format($product->effective_price, 2) }}</span>

                            @if($product->is_in_stock)
                                <button onclick="addToCart({{ $product->id }})" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 transition-all flex items-center gap-1.5">
                                    <i class="fa-solid fa-cart-plus text-[10px]"></i>
                                    <span>{{ __('messages.add_to_cart') }}</span>
                                </button>
                            @else
                                <span class="text-[10px] text-slate-400 font-bold">Out of Stock</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</div>
@endsection
