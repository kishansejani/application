{{--
    Product card. Usage: @include('frontend.partials.product-card', ['product' => $product])
    Optional: 'showCategory' => false, 'wishlistRemovable' => true (wishlist page: removing the heart removes the card)
--}}
@php
    if (!app()->bound('fx.wishIds')) {
        app()->instance('fx.wishIds', Auth::check() ? Auth::user()->wishlists()->pluck('product_id')->map(fn($v) => (int) $v)->all() : []);
    }
    $inWish = in_array((int) $product->id, app('fx.wishIds'), true);
    $showCategory = $showCategory ?? true;
    $wishlistRemovable = $wishlistRemovable ?? false;
    $productUrl = route('products.show', $product->slug);
    $cardGu = app()->getLocale() === 'gu';
@endphp
<article class="group relative flex flex-col fx-card overflow-hidden hover:shadow-xl hover:shadow-slate-900/5 dark:hover:shadow-black/30 hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-200" data-product-id="{{ $product->id }}">
    <div class="relative aspect-square bg-slate-50 dark:bg-slate-800/60 overflow-hidden">
        <a href="{{ $productUrl }}" class="block w-full h-full" tabindex="-1" aria-hidden="true">
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" loading="lazy" decoding="async"
                 class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 {{ $product->is_in_stock ? '' : 'grayscale opacity-60' }}">
        </a>

        @if($product->has_discount)
            <span class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-600 text-white font-extrabold text-[10px] leading-none shadow-sm">
                <i class="ph-fill ph-seal-percent text-[11px]"></i>{{ $product->discount_percent }}% {{ __('messages.off') }}
            </span>
        @endif

        <button type="button"
                onclick="toggleWishlist({{ $product->id }}, this){{ $wishlistRemovable ? "; $(this).closest('article').fadeOut(200, function(){ $(this).remove(); })" : '' }}"
                data-wishlist-btn="{{ $product->id }}" aria-pressed="{{ $inWish ? 'true' : 'false' }}"
                class="absolute top-2 right-2 w-9 h-9 rounded-full bg-white/90 dark:bg-slate-900/80 backdrop-blur text-slate-500 dark:text-slate-300 hover:text-rose-500 flex items-center justify-center shadow-sm transition-colors"
                title="{{ __('messages.wishlist') }}" aria-label="{{ __('messages.wishlist') }}">
            <i class="fx-heart {{ $inWish ? 'ph-fill heart-active text-rose-600' : 'ph' }} ph-heart text-lg"></i>
        </button>

        @if($product->is_in_stock)
            <button type="button" onclick="openQuickView({{ $product->id }}, @js($productUrl))"
                    class="hidden lg:flex absolute bottom-2.5 inset-x-2.5 items-center justify-center gap-1.5 py-2 rounded-xl bg-white/95 dark:bg-slate-900/90 backdrop-blur text-slate-800 dark:text-white text-[12px] font-bold shadow opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 focus:opacity-100 focus:translate-y-0 transition-all">
                <i class="ph ph-eye"></i>{{ __('messages.quick_view') }}
            </button>
        @else
            <span class="absolute bottom-2.5 left-2.5 px-2 py-1 rounded-lg bg-slate-900/80 text-white text-[10px] font-bold">{{ __('messages.out_of_stock') }}</span>
        @endif
    </div>

    <div class="flex flex-col flex-1 p-3 sm:p-3.5">
        @if($showCategory && $product->category)
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 truncate">{{ $product->category->localized_name }}</span>
        @endif
        <a href="{{ $productUrl }}" class="mt-0.5 font-bold text-[13px] sm:text-sm leading-snug text-slate-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400 line-clamp-2 min-h-[2.5rem]">
            {{ $product->localized_name }}
        </a>
        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">{{ $product->unit }}</span>

        <div class="mt-auto pt-2.5">
            <div class="flex items-baseline gap-1.5 flex-wrap mb-2.5">
                <span class="font-extrabold text-[15px] sm:text-base text-slate-900 dark:text-white">₹{{ number_format($product->effective_price, 2) }}</span>
                @if($product->has_discount)
                    <span class="text-[11px] text-slate-400 line-through">₹{{ number_format($product->price, 2) }}</span>
                @endif
            </div>

            @if($product->is_in_stock)
                <div data-cart-control data-product-id="{{ $product->id }}" data-stock="{{ $product->stock_quantity }}">
                    <button type="button" data-add class="fx-btn fx-btn-soft w-full !h-9 !py-0 !text-[12.5px] border border-brand-200 dark:border-brand-500/30 hover:!bg-brand-600 hover:!text-white hover:border-brand-600">
                        <i class="ph-bold ph-plus"></i><span>{{ $cardGu ? 'ઉમેરો' : 'Add' }}</span>
                    </button>
                    <div data-stepper class="hidden fx-stepper w-full" role="group" aria-label="{{ $cardGu ? 'જથ્થો' : 'Quantity' }}">
                        <button type="button" data-step="-1" aria-label="{{ $cardGu ? 'ઘટાડો' : 'Decrease' }}"><i class="ph-bold ph-minus text-sm"></i></button>
                        <span class="fx-stepper-qty" aria-live="polite">1</span>
                        <button type="button" data-step="1" aria-label="{{ $cardGu ? 'વધારો' : 'Increase' }}"><i class="ph-bold ph-plus text-sm"></i></button>
                    </div>
                </div>
            @else
                <button type="button" disabled class="fx-btn w-full !h-9 !py-0 !text-[12.5px] bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed">{{ __('messages.out_of_stock') }}</button>
            @endif
        </div>
    </div>
</article>
