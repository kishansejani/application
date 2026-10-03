{{-- Order status timeline. Usage: @include('frontend.partials.order-timeline', ['order' => $order]) --}}
@php
    $tlGu = app()->getLocale() === 'gu';
    $tlSteps = [
        'pending' => ['ph-receipt', $tlGu ? 'ઓર્ડર મળ્યો' : 'Order placed'],
        'confirmed' => ['ph-check-circle', $tlGu ? 'સ્વીકારાયેલ' : 'Confirmed'],
        'processing' => ['ph-package', $tlGu ? 'પેકિંગ' : 'Packing'],
        'out_for_delivery' => ['ph-truck', $tlGu ? 'ડિલિવરી માટે નીકળેલ' : 'Out for delivery'],
        'delivered' => ['ph-house-line', $tlGu ? 'ડિલિવર થયું' : 'Delivered'],
    ];
    $tlKeys = array_keys($tlSteps);
    $tlCurrent = array_search($order->order_status, $tlKeys, true);
    $tlCancelled = $order->order_status === 'cancelled';
@endphp

@if($tlCancelled)
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-900 dark:text-rose-200">
        <span class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 flex items-center justify-center text-xl shrink-0"><i class="ph-fill ph-prohibit"></i></span>
        <div>
            <p class="font-extrabold text-sm">{{ $tlGu ? 'આ ઓર્ડર રદ કરવામાં આવ્યો છે' : 'This order has been cancelled' }}</p>
            <p class="text-[13px] mt-0.5 opacity-80">{{ $order->cancellation_reason ?: ($tlGu ? 'વિનંતી પર રદ' : 'Cancelled upon request') }}</p>
        </div>
    </div>
@else
    {{-- Horizontal (tablet / desktop) --}}
    <ol class="hidden sm:grid grid-cols-5 relative" aria-label="{{ __('messages.order_progress') }}">
        @foreach($tlSteps as $key => [$icon, $label])
            @php $i = $loop->index; $done = $tlCurrent !== false && $i < $tlCurrent; $now = $tlCurrent === $i; @endphp
            <li class="relative flex flex-col items-center text-center px-1" @if($now) aria-current="step" @endif>
                @if(!$loop->first)
                    <span class="absolute top-5 right-1/2 w-full h-1 -translate-y-1/2 rounded-full {{ ($tlCurrent !== false && $i <= $tlCurrent) ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                @endif
                <span class="relative z-10 w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all
                    {{ $done ? 'bg-brand-600 text-white' : ($now ? 'bg-brand-600 text-white ring-4 ring-brand-500/20 shadow-lg shadow-brand-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500') }}">
                    <i class="{{ $done ? 'ph-bold ph-check' : ($now ? 'ph-fill ' . $icon : 'ph ' . $icon) }}"></i>
                </span>
                <span class="mt-2.5 text-[12px] font-bold leading-tight {{ ($done || $now) ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">{{ $label }}</span>
                @if($key === 'pending')
                    <span class="text-[11px] text-slate-400 mt-0.5">{{ $order->created_at->format('d M, h:i A') }}</span>
                @elseif($key === 'delivered' && $order->estimated_delivery_at && !$now)
                    <span class="text-[11px] text-slate-400 mt-0.5">ETA {{ $order->estimated_delivery_at->format('d M, h:i A') }}</span>
                @elseif($now)
                    <span class="text-[11px] font-bold text-brand-600 dark:text-brand-400 mt-0.5">{{ $tlGu ? 'હાલની સ્થિતિ' : 'Current status' }}</span>
                @endif
            </li>
        @endforeach
    </ol>

    {{-- Vertical (mobile) --}}
    <ol class="sm:hidden space-y-0" aria-label="{{ __('messages.order_progress') }}">
        @foreach($tlSteps as $key => [$icon, $label])
            @php $i = $loop->index; $done = $tlCurrent !== false && $i < $tlCurrent; $now = $tlCurrent === $i; @endphp
            <li class="relative flex gap-3 {{ $loop->last ? '' : 'pb-5' }}" @if($now) aria-current="step" @endif>
                @if(!$loop->last)
                    <span class="absolute left-[1.0625rem] top-9 bottom-0 w-0.5 rounded-full {{ ($tlCurrent !== false && $i < $tlCurrent) ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                @endif
                <span class="relative z-10 w-9 h-9 rounded-full flex items-center justify-center shrink-0
                    {{ $done ? 'bg-brand-600 text-white' : ($now ? 'bg-brand-600 text-white ring-4 ring-brand-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500') }}">
                    <i class="{{ $done ? 'ph-bold ph-check' : ($now ? 'ph-fill ' . $icon : 'ph ' . $icon) }}"></i>
                </span>
                <div class="pt-1.5 min-w-0">
                    <p class="text-[13px] font-bold {{ ($done || $now) ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">{{ $label }}</p>
                    @if($key === 'pending')
                        <p class="text-[11px] text-slate-400">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                    @elseif($now)
                        <p class="text-[11px] font-bold text-brand-600 dark:text-brand-400">{{ $tlGu ? 'હાલની સ્થિતિ' : 'Current status' }}</p>
                    @elseif($key === 'delivered' && $order->estimated_delivery_at)
                        <p class="text-[11px] text-slate-400">ETA {{ $order->estimated_delivery_at->format('d M Y, h:i A') }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
