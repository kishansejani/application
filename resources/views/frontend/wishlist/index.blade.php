@extends('frontend.layouts.app')

@section('title', __('messages.wishlist') . ' - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <div class="flex items-end justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.wishlist') }}</h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'ઝડપી ફરી ઓર્ડર માટે સાચવેલી વસ્તુઓ' : 'Your saved items for quick re-ordering' }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 font-bold text-[12px] shrink-0">
            <i class="ph-fill ph-heart"></i><span data-wishlist-count data-count="{{ $wishlists->count() }}">{{ $wishlists->count() }}</span> {{ $gu ? 'સાચવેલી' : 'saved' }}
        </span>
    </div>

    @if($wishlists->isEmpty())
        <div class="fx-card max-w-lg mx-auto py-14 px-6 text-center">
            <span class="w-20 h-20 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-500 flex items-center justify-center text-4xl mx-auto mb-4"><i class="ph-duotone ph-heart"></i></span>
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'તમારી વિશલિસ્ટ ખાલી છે' : 'Your wishlist is empty' }}</h3>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'કોઈપણ ઉત્પાદન પર હાર્ટ ટેપ કરીને સાચવો.' : 'Save items you love by tapping the heart on any product.' }}</p>
            <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary fx-btn-lg mt-6">{{ $gu ? 'ઉત્પાદનો જુઓ' : 'Explore products' }}<i class="ph-bold ph-arrow-right"></i></a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-5">
            @foreach($wishlists as $wish)
                @if($wish->product)
                    @include('frontend.partials.product-card', ['product' => $wish->product, 'wishlistRemovable' => true])
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
