{{-- Localised delivery window for an order (orders store the English slot text). Usage: @include('frontend.partials.slot-text', ['order' => $order]) --}}
@php
    $stEta = $order->estimated_delivery_at ? \Illuminate\Support\Carbon::parse($order->estimated_delivery_at) : null;
@endphp
@if(app()->getLocale() === 'gu' && $stEta && in_array($order->delivery_type, ['two_hours', 'next_day'], true)){{ $order->delivery_type === 'two_hours' ? __('messages.slot_two_hours', ['time' => $stEta->format('h:i A')]) : __('messages.slot_next_day', ['date' => $stEta->format('d M')]) }}@else{{ $order->delivery_slot }}@endif
