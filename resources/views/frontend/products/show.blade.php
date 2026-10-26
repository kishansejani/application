@extends('frontend.layouts.app')

@section('title', $product->localized_name . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $inWish = Auth::check() && Auth::user()->wishlists()->where('product_id', $product->id)->exists();
    $gallery = collect([$product->thumbnail_url])->merge($product->images->map(fn($i) => $i->image_url))->unique()->values();
@endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-10 sm:space-y-14">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-400 overflow-x-auto no-scrollbar whitespace-nowrap" aria-label="{{ __('messages.breadcrumb') }}">
        <a href="{{ route('home') }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ __('messages.home') }}</a>
        @if($product->category)
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <a href="{{ route('categories.show', $product->category->slug) }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ $product->category->localized_name }}</a>
        @endif
        @if($product->subCategory)
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <a href="{{ route('subcategories.show', $product->subCategory->slug) }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ $product->subCategory->localized_name }}</a>
        @endif
        <i class="ph-bold ph-caret-right text-[10px]"></i>
        <span class="text-slate-700 dark:text-slate-200 truncate">{{ $product->localized_name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12 -mt-6 sm:-mt-8">
        <!-- Gallery -->
        @php
            $has360 = $product->has_360;
            $ss = $sysSettings ?? \App\Models\Setting::getAllSettings();
        @endphp
        <div class="space-y-3 lg:sticky lg:top-36 self-start" data-product-media>
            @php
                $pd3   = ($ss['fx_product3d'] ?? '1') === '1' && !empty($product->thumbnail_url);
                $modes = array_values(array_filter([$pd3 ? '3d' : null, $has360 ? 'spin' : null, 'photos']));
                $first = $has360 ? 'spin' : ($pd3 ? '3d' : 'photos');
                $modeMeta = [
                    '3d'     => ['ph-cube', '3D'],
                    'spin'   => ['ph-arrows-clockwise', '360°'],
                    'photos' => ['ph-images', $gu ? 'ફોટા' : 'Photos'],
                ];
            @endphp
            @if(count($modes) > 1)
                <div class="media-tabs" style="grid-template-columns: repeat({{ count($modes) }}, 1fr); --n: {{ count($modes) }}" role="tablist" aria-label="{{ $gu ? 'મીડિયા પ્રકાર' : 'Media type' }}">
                    @foreach($modes as $m)
                        <button type="button" role="tab" class="media-tab {{ $m === $first ? 'is-active' : '' }}" data-media-tab="{{ $m }}" aria-selected="{{ $m === $first ? 'true' : 'false' }}">
                            <i class="ph-bold {{ $modeMeta[$m][0] }}"></i>{{ $modeMeta[$m][1] }}
                        </button>
                    @endforeach
                    <span class="media-tab-pill" aria-hidden="true"></span>
                </div>
            @endif

            @if($pd3)
                <div id="media3d" class="relative aspect-square rounded-3xl overflow-hidden fx-card !rounded-3xl prod3d-stage {{ $first !== '3d' ? 'hidden' : '' }}">
                    <div data-product3d data-src="{{ $product->thumbnail_url }}" class="absolute inset-0"
                         data-autoplay="{{ $ss['spin_autoplay'] }}" data-speed="{{ $ss['spin_speed'] }}" data-sensitivity="{{ $ss['spin_sensitivity'] }}"
                         data-hint="{{ $ss['spin_hint'] }}" data-hint-text="{{ $gu ? 'ફેરવવા ખેંચો' : 'Drag to rotate in 3D' }}"
                         aria-label="{{ $product->localized_name }} – 3D"></div>
                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" class="prod3d-fallback absolute inset-0 w-full h-full object-contain p-6" loading="eager">
                    @if($product->has_discount)
                        <span class="absolute bottom-3 left-3 z-10 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-600 text-white font-extrabold text-xs shadow">
                            <i class="ph-fill ph-seal-percent"></i>{{ $product->discount_percent }}% {{ __('messages.off') }}
                        </span>
                    @endif
                </div>
            @endif

            @if($has360)
                <div id="mediaSpin" class="relative aspect-square rounded-3xl overflow-hidden fx-card !rounded-3xl {{ $first !== 'spin' ? 'hidden' : '' }}">
                    <div data-spin360
                         data-frames='@json($product->spin_frame_urls)'
                         data-eager="1"
                         data-autoplay="{{ $ss['spin_autoplay'] }}"
                         data-speed="{{ $ss['spin_speed'] }}"
                         data-sensitivity="{{ $ss['spin_sensitivity'] }}"
                         data-wheel="{{ $ss['spin_wheel'] }}"
                         data-hint="{{ $ss['spin_hint'] }}"
                         aria-label="{{ $product->localized_name }} – 360°"></div>
                    @if($product->has_discount)
                        <span class="absolute top-3 right-3 z-10 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-600 text-white font-extrabold text-xs shadow">
                            <i class="ph-fill ph-seal-percent"></i>{{ $product->discount_percent }}% {{ __('messages.off') }}
                        </span>
                    @endif
                </div>
            @endif

            <div id="mediaPhotos" class="relative aspect-square rounded-3xl overflow-hidden fx-card !rounded-3xl bg-slate-50 dark:bg-slate-800/60 {{ $first !== 'photos' ? 'hidden' : '' }}">
                <img id="mainProductImage" src="{{ $product->thumbnail_url }}" alt="{{ $product->localized_name }}" class="w-full h-full object-cover transition-opacity duration-200">
                @if($product->has_discount)
                    <span class="absolute top-4 left-4 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-600 text-white font-extrabold text-xs shadow">
                        <i class="ph-fill ph-seal-percent"></i>{{ $product->discount_percent }}% {{ __('messages.off') }}
                    </span>
                @endif
            </div>
            @if($gallery->count() > 1)
                <div id="mediaThumbs" class="flex items-center gap-2.5 overflow-x-auto no-scrollbar pb-1 {{ $first !== 'photos' ? 'hidden' : '' }}" role="list">
                    @foreach($gallery as $img)
                        <button type="button" onclick="swapMainImage(@js($img), this)" role="listitem"
                                class="fx-thumb w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden shrink-0 border-2 {{ $loop->first ? 'border-brand-600' : 'border-transparent opacity-70 hover:opacity-100' }} bg-slate-100 dark:bg-slate-800 transition">
                            <img src="{{ $img }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Details -->
        <div class="space-y-5">
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    @if($product->category)
                        <a href="{{ route('categories.show', $product->category->slug) }}" class="px-2.5 py-1 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300 font-bold text-[11px] uppercase tracking-wider">{{ $product->category->localized_name }}</a>
                    @endif
                    @if($product->sku)<span class="text-[11px] text-slate-400 font-mono">SKU {{ $product->sku }}</span>@endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">{{ $product->localized_name }}</h1>
                <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.unit') }}: <span class="text-slate-800 dark:text-slate-200">{{ $product->unit }}</span></p>
            </div>

            <div class="flex items-baseline flex-wrap gap-x-3 gap-y-1">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">₹{{ number_format($product->effective_price, 2) }}</span>
                @if($product->has_discount)
                    <span class="text-base text-slate-400 line-through">{{ __('messages.mrp') }} ₹{{ number_format($product->price, 2) }}</span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 font-extrabold text-xs">
                        {{ __('messages.save') }} ₹{{ number_format($product->price - $product->discount_price, 2) }}
                    </span>
                @endif
                <span class="basis-full text-[11px] text-slate-400">{{ $gu ? 'બધા કર સહિત' : 'Inclusive of all taxes' }}</span>
            </div>

            <!-- Stock -->
            <div class="text-[13px] font-bold">
                @if($product->stock_quantity <= 0)
                    <span class="inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400"><i class="ph-fill ph-x-circle"></i>{{ __('messages.out_of_stock') }}</span>
                @elseif($product->is_low_stock)
                    <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400"><i class="ph-fill ph-warning"></i>{{ __('messages.low_stock', ['count' => $product->stock_quantity]) }}</span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400"><i class="ph-fill ph-check-circle"></i>{{ __('messages.in_stock') }} <span class="font-semibold text-slate-400">· {{ $product->stock_quantity }} {{ $gu ? 'ઉપલબ્ધ' : 'available' }}</span></span>
                @endif
            </div>

            <!-- Purchase -->
            <div id="mainPurchase" class="flex items-stretch gap-3">
                @if($product->is_in_stock)
                    <div class="flex items-center h-12 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                        <button type="button" onclick="adjustQty(-1)" class="w-11 h-full text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white" aria-label="{{ $gu ? 'ઘટાડો' : 'Decrease' }}"><i class="ph-bold ph-minus"></i></button>
                        <input type="number" id="detailQtyInput" value="1" min="1" max="{{ $product->stock_quantity }}" class="fx-noarrows w-10 h-full bg-transparent text-center font-extrabold text-slate-900 dark:text-white outline-none" aria-label="{{ $gu ? 'જથ્થો' : 'Quantity' }}">
                        <button type="button" onclick="adjustQty(1)" class="w-11 h-full text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white" aria-label="{{ $gu ? 'વધારો' : 'Increase' }}"><i class="ph-bold ph-plus"></i></button>
                    </div>
                    <button type="button" id="detailAddBtn" onclick="addToCart({{ $product->id }}, parseInt(document.getElementById('detailQtyInput').value), { button: this })" class="fx-btn fx-btn-primary fx-btn-lg flex-1 !h-12">
                        <i class="ph-bold ph-shopping-cart-simple text-lg"></i><span>{{ __('messages.add_to_cart') }}</span>
                    </button>
                @else
                    <button type="button" disabled class="fx-btn flex-1 !h-12 bg-slate-100 dark:bg-slate-800 text-slate-400">{{ __('messages.out_of_stock') }}</button>
                @endif
                <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" data-wishlist-btn="{{ $product->id }}" aria-pressed="{{ $inWish ? 'true' : 'false' }}"
                        class="w-12 h-12 shrink-0 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500 hover:text-rose-500 hover:border-rose-200 flex items-center justify-center transition-colors relative" title="{{ __('messages.wishlist') }}" aria-label="{{ __('messages.wishlist') }}">
                    <i class="fx-heart {{ $inWish ? 'ph-fill heart-active text-rose-600' : 'ph' }} ph-heart text-xl"></i>
                </button>
            </div>

            <!-- Delivery -->
            <div class="rounded-2xl border border-emerald-200 dark:border-emerald-500/25 bg-gradient-to-br from-emerald-50 to-teal-50/50 dark:from-emerald-500/10 dark:to-teal-500/5 p-4 flex gap-3">
                <span class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 text-amber-500 flex items-center justify-center text-xl shrink-0 shadow-sm"><i class="ph-fill ph-lightning"></i></span>
                <div class="text-[13px]">
                    <p class="font-extrabold text-emerald-900 dark:text-emerald-200">{{ trim(str_replace('⚡', '', __('messages.delivery_promise_title'))) }}</p>
                    <p class="text-emerald-800/80 dark:text-emerald-200/70 text-[12px]">{{ __('messages.delivery_promise_desc') }}</p>
                    <p class="mt-1.5 font-bold text-slate-900 dark:text-white">{{ __('messages.est_delivery') }}: <span class="text-brand-700 dark:text-brand-400">{{ $gu ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center">
                @foreach([['ph-plant', $gu ? 'તાજું' : 'Farm fresh'], ['ph-arrow-counter-clockwise', $gu ? 'સરળ રિટર્ન' : 'Easy returns'], ['ph-money', $gu ? 'COD ઉપલબ્ધ' : 'COD available']] as [$ic, $lbl])
                    <div class="fx-card !rounded-2xl py-3 px-2">
                        <i class="ph-duotone {{ $ic }} text-xl text-brand-600 dark:text-brand-400"></i>
                        <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 mt-1">{{ $lbl }}</p>
                    </div>
                @endforeach
            </div>

            @if($product->localized_short_description)
                <p class="text-[14px] text-slate-600 dark:text-slate-300 leading-relaxed">{{ $product->localized_short_description }}</p>
            @endif

            @if($product->localized_description)
                <details class="fx-card group" open>
                    <summary class="flex items-center justify-between cursor-pointer list-none p-4 font-extrabold text-slate-900 dark:text-white">
                        {{ __('messages.view_details') }}
                        <i class="ph-bold ph-caret-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-4 pb-4 text-[14px] text-slate-600 dark:text-slate-300 leading-relaxed">
                        {!! nl2br(e($product->localized_description)) !!}
                    </div>
                </details>
            @endif
        </div>
    </div>

    <!-- Related -->
    @if($relatedProducts->count() > 0)
        <section aria-labelledby="relatedTitle">
            <h2 id="relatedTitle" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-4">{{ __('messages.related_products') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
                @foreach($relatedProducts as $rel)
                    @include('frontend.partials.product-card', ['product' => $rel, 'showCategory' => false])
                @endforeach
            </div>
        </section>
    @endif

    <!-- Recently viewed -->
    <section class="hidden" data-recently-viewed data-exclude="{{ $product->id }}" aria-labelledby="rvTitle">
        <div class="flex items-end justify-between gap-4 mb-4">
            <h2 id="rvTitle" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'તાજેતરમાં જોયેલા' : 'Recently viewed' }}</h2>
            <button type="button" data-rv-clear class="text-[12px] font-bold text-slate-500 hover:text-rose-600">{{ $gu ? 'સાફ કરો' : 'Clear' }}</button>
        </div>
        <div class="fx-rail" data-rv-rail></div>
    </section>
</div>

<!-- Sticky add-to-cart (mobile/tablet) -->
@if($product->is_in_stock)
    <div id="stickyBuy" class="lg:hidden fixed inset-x-0 z-40 fx-above-tabbar translate-y-[140%] transition-transform duration-300" aria-hidden="true">
        <div class="mx-3 mb-2 p-2.5 pl-3 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur border border-slate-200 dark:border-slate-700 shadow-2xl flex items-center gap-3">
            <img src="{{ $product->thumbnail_url }}" alt="" class="w-11 h-11 rounded-xl object-cover bg-slate-100 dark:bg-slate-800">
            <div class="min-w-0 flex-1">
                <p class="text-[12px] font-bold text-slate-900 dark:text-white truncate">{{ $product->localized_name }}</p>
                <p class="text-sm font-extrabold text-slate-900 dark:text-white">₹{{ number_format($product->effective_price, 2) }} <span class="text-[11px] font-semibold text-slate-400">/ {{ $product->unit }}</span></p>
            </div>
            <button type="button" onclick="addToCart({{ $product->id }}, parseInt(document.getElementById('detailQtyInput').value), { button: this })" class="fx-btn fx-btn-primary !h-11 shrink-0">
                <i class="ph-bold ph-shopping-cart-simple"></i><span>{{ $gu ? 'ઉમેરો' : 'Add' }}</span>
            </button>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .media-tabs { position: relative; display: inline-grid; padding: .25rem; border-radius: 1rem; background: var(--fx-card-bg); border: 1px solid var(--fx-card-border); }
    .media-tab { position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .5rem 1.1rem; font-size: .8rem; font-weight: 800; color: var(--fx-text-muted); border-radius: .75rem; transition: color .25s; }
    .media-tab.is-active { color: #fff; }
    .media-tab-pill { position: absolute; z-index: 0; top: .25rem; bottom: .25rem; left: .25rem; width: calc(50% - .25rem); border-radius: .75rem; /* width set by JS for 2 or 3 tabs */ background: linear-gradient(135deg, var(--fx-brand-primary), color-mix(in srgb, var(--fx-brand-primary) 60%, #7c3aed)); box-shadow: 0 8px 18px -8px color-mix(in srgb, var(--fx-brand-primary) 70%, transparent); transition: transform .35s cubic-bezier(.2,.8,.2,1); }
    .prod3d-stage { background: radial-gradient(65% 55% at 50% 55%, color-mix(in srgb, var(--fx-brand-primary) 16%, transparent), transparent 72%), var(--fx-card-bg); }
    .prod3d-stage.is-3d-ready .prod3d-fallback, .prod3d-stage:has(.is-3d-ready) .prod3d-fallback { opacity: 0; pointer-events: none; transition: opacity .5s; }
</style>
@endpush

@push('scripts')
<script>
    function swapMainImage(url, btn) {
        const img = document.getElementById('mainProductImage');
        if (window.__showPhotos && document.getElementById('mediaPhotos').classList.contains('hidden')) window.__showPhotos();
        img.style.opacity = .3;
        setTimeout(() => { img.src = url; img.style.opacity = 1; }, 120);
        if (btn) {
            document.querySelectorAll('.fx-thumb').forEach(t => { t.classList.remove('border-brand-600'); t.classList.add('border-transparent', 'opacity-70'); });
            btn.classList.add('border-brand-600'); btn.classList.remove('border-transparent', 'opacity-70');
        }
    }

    // 3D / 360° / Photos tabs
    (function () {
        const root = document.querySelector('[data-product-media]');
        if (!root) return;
        const tabs = root.querySelectorAll('[data-media-tab]');
        if (!tabs.length) return;
        const panes = { '3d': document.getElementById('media3d'), spin: document.getElementById('mediaSpin'), photos: document.getElementById('mediaPhotos') };
        const thumbs = document.getElementById('mediaThumbs');
        const pill = root.querySelector('.media-tab-pill');
        const n = tabs.length;
        if (pill) pill.style.width = 'calc(' + (100 / n) + '% - .25rem)';
        function show(which) {
            Object.keys(panes).forEach(function (k) { if (panes[k]) panes[k].classList.toggle('hidden', k !== which); });
            if (thumbs) thumbs.classList.toggle('hidden', which !== 'photos');
            tabs.forEach(function (t, i) {
                const on = t.dataset.mediaTab === which;
                t.classList.toggle('is-active', on); t.setAttribute('aria-selected', on ? 'true' : 'false');
                if (on && pill) pill.style.transform = 'translateX(' + (i * 100) + '%)';
            });
            if (which === 'spin' && window.Spin360) Spin360.refresh(panes.spin);
            if (which === '3d') { if (window.Scene3D) Scene3D.mountAll(panes['3d']); else window.addEventListener('scene3d:ready', function () { Scene3D.mountAll(panes['3d']); }, { once: true }); }
        }
        tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.dataset.mediaTab); }); });
        window.__showPhotos = function () { show('photos'); };
        show(root.querySelector('.media-tab.is-active').dataset.mediaTab);
    })();

    function adjustQty(delta) {
        const input = document.getElementById('detailQtyInput');
        let val = parseInt(input.value) || 1;
        val = Math.max(1, Math.min({{ (int) $product->stock_quantity }}, val + delta));
        input.value = val;
    }

    // Remember this product in "recently viewed" (this browser only)
    FX.recent.push({
        id: {{ $product->id }},
        url: @js(route('products.show', $product->slug)),
        name: @js($product->localized_name),
        img: @js($product->thumbnail_url),
        unit: @js($product->unit),
        price: @js('₹' . number_format($product->effective_price, 2))
    });

    // Sticky mobile buy bar appears once the main purchase row scrolls away
    (function () {
        const bar = document.getElementById('stickyBuy'), target = document.getElementById('mainPurchase');
        if (!bar || !target || !('IntersectionObserver' in window)) return;
        new IntersectionObserver(([e]) => {
            const show = !e.isIntersecting && e.boundingClientRect.top < 0;
            bar.classList.toggle('translate-y-[140%]', !show);
            bar.setAttribute('aria-hidden', show ? 'false' : 'true');
        }).observe(target);
    })();
</script>
@endpush
