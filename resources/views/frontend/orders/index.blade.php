@extends('frontend.layouts.app')

@section('title', __('messages.order_history') . ' - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <div class="flex items-end justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.order_history') }}</h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'તમારા જૂના ઓર્ડર ટ્રેક કરો અને ઇન્વૉઇસ ડાઉનલોડ કરો' : 'Track past purchases and download tax invoices' }}</p>
        </div>
        <a href="{{ route('order.track') }}" class="fx-btn fx-btn-outline shrink-0"><i class="ph ph-crosshair text-lg"></i><span class="hidden sm:inline">{{ $gu ? 'ડિલિવરી ટ્રેક કરો' : 'Track delivery' }}</span></a>
    </div>

    <div class="space-y-4">
        @forelse($orders as $order)
            <article class="fx-card overflow-hidden">
                <header class="px-4 sm:px-6 py-3.5 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
                    <div class="min-w-0">
                        <a href="{{ route('order.show', $order->order_number) }}" class="font-extrabold text-[14px] text-slate-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400">#{{ $order->order_number }}</a>
                        <p class="text-[12px] text-slate-500 dark:text-slate-400">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $order->delivery_type === 'two_hours' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' }}">
                            <i class="ph-fill {{ $order->delivery_type === 'two_hours' ? 'ph-lightning' : 'ph-calendar-check' }}"></i>{{ $order->delivery_type === 'two_hours' ? ($gu ? '૨ કલાક' : '2-hour express') : ($gu ? 'આગલા દિવસે' : 'Next day') }}
                        </span>
                        @include('frontend.partials.status-pill', ['order' => $order])
                    </div>
                </header>

                <div class="px-4 sm:px-6 py-4 flex items-center gap-3">
                    <div class="flex -space-x-3 shrink-0">
                        @foreach($order->items->take(4) as $item)
                            <img src="{{ $item->product ? $item->product->thumbnail_url : ($item->product_image ?: asset('images/default-product.png')) }}" alt="" class="w-12 h-12 rounded-xl object-cover bg-slate-100 dark:bg-slate-800 ring-2 ring-white dark:ring-slate-900" loading="lazy">
                        @endforeach
                        @if($order->items->count() > 4)
                            <span class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 ring-2 ring-white dark:ring-slate-900 flex items-center justify-center text-[12px] font-bold text-slate-600 dark:text-slate-300">+{{ $order->items->count() - 4 }}</span>
                        @endif
                    </div>
                    <p class="flex-1 min-w-0 text-[13px] text-slate-600 dark:text-slate-300 line-clamp-2">
                        {{ $order->items->map(fn($i) => ($gu && $i->product_name_gu ? $i->product_name_gu : $i->product_name_en) . ' × ' . $i->quantity)->implode(', ') }}
                    </p>
                </div>

                <footer class="px-4 sm:px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-[17px] font-extrabold text-slate-900 dark:text-white leading-tight">₹{{ number_format($order->total_amount, 2) }}</p>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold">{{ \Lang::has('messages.payment_methods.' . $order->payment_method) ? __('messages.payment_methods.' . $order->payment_method) : strtoupper($order->payment_method) }} · {{ \Lang::has('messages.payment_statuses.' . $order->payment_status) ? __('messages.payment_statuses.' . $order->payment_status) : ucfirst($order->payment_status) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="fx-btn fx-btn-outline fx-btn-sm"><i class="ph ph-receipt text-base"></i>{{ $gu ? 'ઇન્વૉઇસ' : 'Invoice' }}</a>
                        <a href="{{ route('order.show', $order->order_number) }}" class="fx-btn fx-btn-primary fx-btn-sm">{{ $gu ? 'વિગતો જુઓ' : 'View details' }}<i class="ph-bold ph-caret-right"></i></a>
                    </div>
                </footer>
            </article>
        @empty
            <div class="fx-card py-14 px-6 text-center">
                <span class="w-20 h-20 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-4xl mx-auto mb-4"><i class="ph-duotone ph-package"></i></span>
                <h4 class="font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'હજી સુધી કોઈ ઓર્ડર નથી' : "You haven't placed any orders yet" }}</h4>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'બપોરે ૧૨ પહેલાં ઓર્ડર કરો અને ૨ કલાકમાં મેળવો!' : 'Order before 12 PM to receive your groceries in 2 hours!' }}</p>
                <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary mt-5">{{ __('messages.start_shopping') }}</a>
            </div>
        @endforelse
    </div>

    <div class="pt-6">{{ $orders->links('frontend.partials.pagination') }}</div>
</div>
@endsection
