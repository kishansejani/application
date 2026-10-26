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
<article class="mk-card group" data-product-id="{{ $product->id }}">
    <div class="mk-img">
        <a href="{{ $productUrl }}" class="block w-full h-full" tabindex="-1" aria-hidden="true">
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" loading="lazy" decoding="async"
                 class="{{ $product->is_in_stock ? '' : 'grayscale opacity-60' }}">
        </a>

        @if($product->has_discount)
            <span class="mk-off"><span>{{ $product->discount_percent }}%<small>{{ strtoupper(__('messages.off')) }}</small></span></span>
        @endif

        @if($product->has_360)
            <span class="fx-badge-360" title="360°"><i class="ph-bold ph-arrows-clockwise"></i>360°</span>
        @endif

        <button type="button"
                onclick="toggleWishlist({{ $product->id }}, this){{ $wishlistRemovable ? "; $(this).closest('article').fadeOut(200, function(){ $(this).remove(); })" : '' }}"
                data-wishlist-btn="{{ $product->id }}" aria-pressed="{{ $inWish ? 'true' : 'false' }}"
                class="mk-heart" title="{{ __('messages.wishlist') }}" aria-label="{{ __('messages.wishlist') }}">
            <i class="fx-heart {{ $inWish ? 'ph-fill heart-active text-rose-600' : 'ph' }} ph-heart text-lg"></i>
        </button>

        @if($product->is_in_stock)
            <button type="button" onclick="openQuickView({{ $product->id }}, @js($productUrl))" class="mk-qv" title="{{ __('messages.quick_view') }}" aria-label="{{ __('messages.quick_view') }}">
                <i class="ph ph-eye"></i>
            </button>

            <div class="mk-cart" data-cart-control data-product-id="{{ $product->id }}" data-stock="{{ $product->stock_quantity }}">
                <button type="button" data-add aria-label="{{ $cardGu ? 'ઉમેરો' : 'Add to cart' }}">
                    <i class="ph-bold ph-plus"></i><span>{{ $cardGu ? 'ઉમેરો' : 'Add' }}</span>
                </button>
                <div data-stepper class="hidden fx-stepper" role="group" aria-label="{{ $cardGu ? 'જથ્થો' : 'Quantity' }}">
                    <button type="button" data-step="-1" aria-label="{{ $cardGu ? 'ઘટાડો' : 'Decrease' }}"><i class="ph-bold ph-minus text-sm"></i></button>
                    <span class="fx-stepper-qty" aria-live="polite">1</span>
                    <button type="button" data-step="1" aria-label="{{ $cardGu ? 'વધારો' : 'Increase' }}"><i class="ph-bold ph-plus text-sm"></i></button>
                </div>
            </div>
        @else
            <span class="mk-oos">{{ __('messages.out_of_stock') }}</span>
        @endif
    </div>

    <div class="mk-body">
        @if($showCategory && $product->category)
            <span class="mk-cat-l truncate">{{ $product->category->localized_name }}</span>
        @endif
        <a href="{{ $productUrl }}" class="mk-name">{{ $product->localized_name }}</a>
        <div class="mk-meta">
            <div class="mk-price">
                <b>₹{{ number_format($product->effective_price, 2) }}</b>
                @if($product->has_discount)<s>₹{{ number_format($product->price, 2) }}</s>@endif
            </div>
            @if($product->unit)<span class="mk-unit">{{ $product->unit }}</span>@endif
        </div>
    </div>
</article>
