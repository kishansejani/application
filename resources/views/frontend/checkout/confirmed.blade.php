@extends('frontend.layouts.app')

@section('title', 'Order Confirmed - ' . $order->order_number)

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-12 space-y-5">

    <div class="fx-card relative overflow-hidden p-6 sm:p-10 text-center">
        <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-emerald-50 to-transparent dark:from-emerald-500/10"></div>
        <div class="relative">
            <span class="w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center text-4xl mx-auto shadow-xl shadow-emerald-500/30 ring-8 ring-emerald-100 dark:ring-emerald-500/15">
                <i class="ph-bold ph-check"></i>
            </span>
            <h1 class="mt-5 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.order_confirmed') }}</h1>
            <p class="mt-2 text-[14px] text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                {{ $gu ? 'ફ્રેશ એક્સપ્રેસ પર ઓર્ડર કરવા બદલ આભાર! અમે તમારી વસ્તુઓ સ્વચ્છતાથી પેક કરી રહ્યા છીએ.' : 'Thank you for shopping with FreshExpress! We are packing your items with care.' }}
            </p>
            <div class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-900 dark:text-amber-200 text-[13px] font-bold text-left">
                <i class="ph-fill ph-lightning text-amber-500 text-lg shrink-0"></i>
                <span>@include('frontend.partials.slot-text', ['order' => $order])</span>
            </div>
        </div>
    </div>

    <div class="fx-card p-4 sm:p-6 space-y-5">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('messages.order_number') }}</dt>
                <dd class="mt-0.5 flex items-center gap-1.5">
                    <span class="font-extrabold text-[13px] text-brand-700 dark:text-brand-400 break-all">{{ $order->order_number }}</span>
                    <button type="button" data-copy="{{ $order->order_number }}" class="text-slate-400 hover:text-brand-600 shrink-0" aria-label="{{ __('messages.copy') }}"><i class="ph ph-copy"></i></button>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $gu ? 'ઇન્વૉઇસ' : 'Invoice' }}</dt>
                <dd class="mt-0.5 font-bold text-[13px] text-slate-900 dark:text-white">{{ $order->invoice_number }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $gu ? 'ચુકવણી' : 'Payment' }}</dt>
                <dd class="mt-0.5 font-bold text-[13px] text-slate-900 dark:text-white">{{ \Lang::has('messages.payment_methods.' . $order->payment_method) ? __('messages.payment_methods.' . $order->payment_method) : strtoupper($order->payment_method) }} <span class="text-[11px] font-semibold text-slate-400">({{ \Lang::has('messages.payment_statuses.' . $order->payment_status) ? __('messages.payment_statuses.' . $order->payment_status) : ucfirst($order->payment_status) }})</span></dd>
            </div>
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $gu ? 'કુલ રકમ' : 'Total' }}</dt>
                <dd class="mt-0.5 font-extrabold text-[15px] text-slate-900 dark:text-white">₹{{ number_format($order->total_amount, 2) }}</dd>
            </div>
        </dl>

        <div class="flex gap-3">
            <span class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0"><i class="ph-fill ph-map-pin"></i></span>
            <div class="text-[13px]">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $gu ? 'ડિલિવરી સરનામું' : 'Delivering to' }}</p>
                <p class="font-bold text-slate-900 dark:text-white">{{ $order->customer_name }} <span class="font-mono font-semibold text-slate-500">· +91 {{ $order->customer_phone }}</span></p>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">{{ $order->delivery_address }}</p>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">{{ $gu ? 'આ ડિલિવરીમાં વસ્તુઓ' : 'Items in this delivery' }}</p>
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($order->items as $item)
                    <li class="py-2.5 flex items-center justify-between gap-3 text-[13px]">
                        <span class="min-w-0">
                            <span class="font-bold text-slate-900 dark:text-white">{{ $gu && $item->product_name_gu ? $item->product_name_gu : $item->product_name_en }}</span>
                            <span class="text-slate-400 font-medium"> · {{ $item->product_unit }} × {{ $item->quantity }}</span>
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white shrink-0">₹{{ number_format($item->total_price, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="pt-5 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank" class="fx-btn fx-btn-outline"><i class="ph ph-receipt text-lg"></i>{{ __('messages.download_invoice') }}</a>
            <a href="{{ route('order.track', ['order_number' => $order->order_number]) }}" class="fx-btn fx-btn-soft"><i class="ph ph-package text-lg"></i>{{ $gu ? 'સ્થિતિ ટ્રેક કરો' : 'Track status' }}</a>
            <a href="{{ route('products.index') }}" class="fx-btn fx-btn-primary">{{ $gu ? 'ખરીદી ચાલુ રાખો' : 'Continue shopping' }}<i class="ph-bold ph-arrow-right"></i></a>
        </div>
    </div>
</div>
@endsection
