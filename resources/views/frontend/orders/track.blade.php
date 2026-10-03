@extends('frontend.layouts.app')

@section('title', 'Track Order Delivery - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-12 space-y-5">

    <div class="text-center">
        <span class="w-14 h-14 rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-3xl mx-auto mb-3"><i class="ph-duotone ph-truck"></i></span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $gu ? 'ઓર્ડર ડિલિવરી ટ્રેક કરો' : 'Track your order' }}</h1>
        <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'લાઇવ અપડેટ જોવા માટે ઓર્ડર નંબર દાખલ કરો' : 'Enter your order number to see live fulfilment updates' }}</p>
    </div>

    <form action="{{ route('order.track') }}" method="GET" class="fx-card p-3 sm:p-4 flex flex-col sm:flex-row gap-2.5" data-loading>
        <label class="relative flex-1">
            <span class="sr-only">{{ __('messages.order_number') }}</span>
            <i class="ph ph-hash absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text" name="order_number" value="{{ request('order_number') }}" required placeholder="ORD-68DF09A…" autocomplete="off" class="fx-input !pl-10 !h-12 font-mono font-bold uppercase">
        </label>
        <button type="submit" class="fx-btn fx-btn-primary !h-12 sm:!px-6"><i class="ph-bold ph-magnifying-glass"></i><span>{{ $gu ? 'સ્થિતિ જુઓ' : 'Track status' }}</span></button>
    </form>

    @if($order)
        <div class="fx-card p-4 sm:p-6 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="min-w-0">
                    <p class="font-extrabold text-[15px] text-slate-900 dark:text-white break-all">#{{ $order->order_number }}</p>
                    <p class="text-[12px] text-slate-500 dark:text-slate-400">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $order->delivery_type === 'two_hours' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' }}">
                        <i class="ph-fill {{ $order->delivery_type === 'two_hours' ? 'ph-lightning' : 'ph-calendar-check' }}"></i>{{ $order->delivery_type === 'two_hours' ? ($gu ? '૨ કલાક' : '2-hour express') : ($gu ? 'આગલા દિવસે' : 'Next day') }}
                    </span>
                    @include('frontend.partials.status-pill', ['order' => $order])
                </div>
            </div>

            @include('frontend.partials.order-timeline', ['order' => $order])

            <dl class="rounded-2xl bg-slate-50 dark:bg-slate-800/50 p-4 text-[13px] space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5 sm:gap-4"><dt class="text-slate-500 dark:text-slate-400 shrink-0">{{ $gu ? 'ડિલિવરી સ્લોટ' : 'Delivery slot' }}</dt><dd class="font-bold text-slate-900 dark:text-white sm:text-right">@include('frontend.partials.slot-text', ['order' => $order])</dd></div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5 sm:gap-4"><dt class="text-slate-500 dark:text-slate-400 shrink-0">{{ $gu ? 'પ્રાપ્તકર્તા' : 'Recipient' }}</dt><dd class="font-bold text-slate-900 dark:text-white sm:text-right">{{ $order->customer_name }}</dd></div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5 sm:gap-4"><dt class="text-slate-500 dark:text-slate-400 shrink-0">{{ $gu ? 'સરનામું' : 'Address' }}</dt><dd class="font-medium text-slate-700 dark:text-slate-200 sm:text-right">{{ $order->delivery_address }}</dd></div>
            </dl>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-2">
                <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="fx-btn fx-btn-outline"><i class="ph ph-receipt text-lg"></i>{{ $gu ? 'ઇન્વૉઇસ' : 'Invoice' }}</a>
                <a href="{{ route('order.show', $order->order_number) }}" class="fx-btn fx-btn-primary">{{ $gu ? 'સંપૂર્ણ વિગતો જુઓ' : 'View full order details' }}<i class="ph-bold ph-arrow-right"></i></a>
            </div>
        </div>
    @elseif(request('order_number'))
        <div class="fx-card p-6 text-center">
            <span class="w-14 h-14 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-500 flex items-center justify-center text-2xl mx-auto mb-3"><i class="ph-duotone ph-magnifying-glass"></i></span>
            <p class="font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'ઓર્ડર મળ્યો નથી' : 'No order found' }}</p>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'કૃપા કરીને ઓર્ડર નંબર તપાસો.' : 'Please double-check the order number and try again.' }}</p>
        </div>
    @endif
</div>
@endsection
