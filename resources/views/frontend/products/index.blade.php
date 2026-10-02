@extends('frontend.layouts.app')

@section('title', __('messages.products') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Breadcrumbs -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.products') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Showing {{ $products->total() }} grocery items with express 2-hour delivery</p>
        </div>

        <!-- Sorting -->
        <form action="{{ route('products.index') }}" method="GET" class="flex items-center gap-2">
            @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
            @if(request('subcategory')) <input type="hidden" name="subcategory" value="{{ request('subcategory') }}"> @endif
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

            <label class="text-xs font-bold text-slate-500 uppercase">{{ __('messages.sort_by') }}:</label>
            <select name="sort" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none">
                <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured Items</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
            </select>
        </form>
    </div>

    <!-- Main Layout (Sidebar Filters & Products Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">

        <!-- Sidebar Filter (1 Col) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-filter text-brand-600"></i>
                    <span>{{ __('messages.filter_by') }}</span>
                </h4>
                @if(request()->hasAny(['category', 'subcategory', 'q', 'min_price', 'max_price']))
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-rose-600 hover:underline">{{ __('messages.clear_all') }}</a>
                @endif
            </div>

            <!-- Category List Filter -->
            <div class="space-y-2">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('messages.categories') }}</h5>
                <div class="space-y-1 max-h-64 overflow-y-auto pr-1 text-xs">
                    <a href="{{ route('products.index') }}" class="flex items-center justify-between py-1.5 px-2 rounded-lg font-medium {{ !request('category') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>{{ __('messages.all_categories') }}</span>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="flex items-center justify-between py-1.5 px-2 rounded-lg font-medium {{ request('category') == $cat->slug ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>{{ $cat->localized_name }}</span>
                            <span class="text-[10px] text-slate-400">{{ $cat->products_count ?? $cat->products->count() }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Price Range (₹)</h5>
                <form action="{{ route('products.index') }}" method="GET" class="space-y-2">
                    @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₹" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₹" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs">
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
                        Apply Price Filter
                    </button>
                </form>
            </div>

            <!-- Delivery Promise Box -->
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-900 text-xs space-y-2">
                <div class="flex items-center gap-2 font-bold">
                    <i class="fa-solid fa-bolt text-amber-500"></i>
                    <span>2-Hour Delivery Active</span>
                </div>
                <p class="text-[11px] text-emerald-800">
                    {{ app()->getLocale() === 'gu'
                        ? 'બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં ડિલિવરી મેળવો!'
                        : 'Order before 12:00 PM for doorstep delivery in 2 hours.'
                    }}
                </p>
            </div>
        </div>

        <!-- Products Grid (3 Cols) -->
        <div class="lg:col-span-3 space-y-8">
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                @forelse($products as $product)
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
                        <h4 class="font-bold text-slate-700 text-base">No products match your filter.</h4>
                        <p class="text-xs text-slate-400 mt-1">Try clearing some filters or searching for something else.</p>
                        <a href="{{ route('products.index') }}" class="inline-block mt-4 px-5 py-2.5 bg-brand-600 text-white rounded-xl text-xs font-bold">Reset Filters</a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="pt-6">
                {{ $products->links() }}
            </div>
        </div>

    </div>

</div>
@endsection
