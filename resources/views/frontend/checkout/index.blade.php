@extends('frontend.layouts.app')

@section('title', __('messages.proceed_to_checkout') . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $isExpress = $deliverySlotInfo['type'] === 'two_hours';
    $oldAddr = old('selected_address_id');
    $useNew = $addresses->count() === 0 || ($oldAddr === null && old('house_no')) || $oldAddr === '';
    $selectedId = $oldAddr ?: ($defaultAddress->id ?? $addresses->first()?->id);
    $stepTitle = 'font-extrabold text-slate-900 dark:text-white text-[15px] flex items-center gap-2.5';
    $stepNum = 'w-7 h-7 rounded-full bg-brand-600 text-white text-[12px] font-extrabold flex items-center justify-center shrink-0';
@endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <div class="flex items-end justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-400 mb-1" aria-label="{{ __('messages.breadcrumb') }}">
                <a href="{{ route('cart.index') }}" class="hover:text-slate-700 dark:hover:text-slate-200">{{ __('messages.cart') }}</a>
                <i class="ph-bold ph-caret-right text-[10px]"></i>
                <span class="text-slate-700 dark:text-slate-200">{{ $gu ? 'ચેકઆઉટ' : 'Checkout' }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.proceed_to_checkout') }}</h1>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 text-[12px] font-semibold text-slate-500 dark:text-slate-400"><i class="ph-fill ph-lock-simple text-emerald-500"></i>{{ $gu ? 'સુરક્ષિત ચેકઆઉટ' : 'Secure checkout' }}</span>
    </div>

    <form id="checkoutForm" action="{{ route('checkout.place_order') }}" method="POST" data-loading novalidate>
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-8 items-start">
            <div class="lg:col-span-2 space-y-4 sm:space-y-5">

                <!-- Delivery slot banner (before 12 PM → 2 hours, else next day) -->
                <div class="relative overflow-hidden rounded-3xl p-5 sm:p-6 text-white {{ $isExpress ? 'bg-gradient-to-br from-emerald-600 to-teal-700' : 'bg-gradient-to-br from-sky-600 to-indigo-700' }}">
                    <i class="ph-duotone {{ $isExpress ? 'ph-lightning' : 'ph-moon-stars' }} absolute -right-4 -bottom-6 text-[9rem] opacity-15"></i>
                    <div class="relative flex flex-wrap items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-400 text-slate-950 font-extrabold text-[10px] uppercase tracking-wider">
                            <i class="ph-fill {{ $isExpress ? 'ph-lightning' : 'ph-calendar-check' }}"></i>{{ $isExpress ? ($gu ? '૨ કલાક એક્સપ્રેસ' : '2-hour express dispatch') : ($gu ? 'આવતીકાલે સવારે ડિલિવરી' : 'Next-day morning delivery') }}
                        </span>
                        <span class="text-[11px] font-semibold text-white/75">{{ $gu ? 'દૈનિક કટઑફ: બપોરે ૧૨:૦૦' : 'Daily cutoff: 12:00 PM' }}</span>
                    </div>
                    <h3 class="relative mt-3 text-lg sm:text-xl font-extrabold leading-snug">{{ $gu ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</h3>
                    <p class="relative mt-1 text-[13px] text-white/80 max-w-xl">
                        {{ $isExpress
                            ? ($gu ? 'તમારો ઓર્ડર બપોરે ૧૨ પહેલાં છે — ૨ કલાકમાં તમારા દરવાજે પહોંચશે.' : 'You are ordering before 12:00 PM — your groceries reach your doorstep within 2 hours.')
                            : ($gu ? 'બપોરે ૧૨ પછીના ઓર્ડર આવતીકાલે સવારે ૯ થી ૧૨ વચ્ચે ડિલિવર થાય છે.' : 'Orders after 12:00 PM are delivered tomorrow morning between 9:00 AM and 12:00 PM.') }}
                    </p>
                </div>

                <!-- 1. Contact -->
                <section class="fx-card p-4 sm:p-6">
                    <h3 class="{{ $stepTitle }} mb-4"><span class="{{ $stepNum }}">1</span>{{ $gu ? 'સંપર્ક વિગતો' : 'Contact details' }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="fx-label">{{ $gu ? 'પૂરું નામ' : 'Full name' }} <span class="text-rose-500">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', Auth::user()?->name) }}" required autocomplete="name" placeholder="{{ __('messages.ph_name') }}" class="fx-input @error('customer_name') is-invalid @enderror">
                            <p id="err_customer_name" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1 hidden"><i class="ph-fill ph-warning-circle"></i><span></span></p>
                            @error('customer_name')<p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer_phone" class="fx-label">{{ $gu ? '૧૦ અંકનો મોબાઇલ નંબર' : '10-digit mobile number' }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px] font-bold text-slate-400">+91</span>
                                <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', Auth::user()?->phone) }}" required pattern="[0-9]{10}" inputmode="numeric" maxlength="10" autocomplete="tel-national" placeholder="9876543210" class="fx-input !pl-12 font-mono @error('customer_phone') is-invalid @enderror">
                            </div>
                            <p id="err_customer_phone" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1 hidden"><i class="ph-fill ph-warning-circle"></i><span></span></p>
                            @error('customer_phone')<p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <!-- 2. Address -->
                <section class="fx-card p-4 sm:p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="{{ $stepTitle }}"><span class="{{ $stepNum }}">2</span>{{ __('messages.select_delivery_address') }}</h3>
                        <button type="button" onclick="openMapModal()" class="fx-btn fx-btn-soft fx-btn-sm shrink-0"><i class="ph ph-map-trifold text-base"></i><span class="hidden xs:inline">{{ __('messages.pick_on_map') }}</span></button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($addresses as $addr)
                            <label class="relative p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 dark:has-[:checked]:bg-brand-500/10 cursor-pointer transition-colors flex items-start gap-3">
                                <input type="radio" name="selected_address_id" value="{{ $addr->id }}" {{ !$useNew && (string) $selectedId === (string) $addr->id ? 'checked' : '' }} class="mt-1 w-4 h-4 accent-emerald-600 shrink-0" data-addr-mode="saved">
                                <span class="text-[13px] min-w-0">
                                    <span class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                                        <i class="ph {{ $addr->type === 'Home' ? 'ph-house' : ($addr->type === 'Work' ? 'ph-briefcase' : 'ph-map-pin') }} text-brand-600"></i>{{ \Lang::has('messages.address_types.' . $addr->type) ? __('messages.address_types.' . $addr->type) : $addr->type }}
                                        @if($addr->is_default)<span class="px-1.5 py-0.5 rounded-md bg-brand-100 dark:bg-brand-500/20 text-brand-700 dark:text-brand-300 text-[10px] uppercase">{{ __('messages.default_address') }}</span>@endif
                                    </span>
                                    <span class="block mt-1 text-slate-700 dark:text-slate-200 font-semibold">{{ $addr->recipient_name }}</span>
                                    <span class="block text-slate-500 dark:text-slate-400 leading-relaxed">{{ $addr->full_address }}</span>
                                    <span class="block text-[12px] text-slate-400 font-mono mt-0.5">+91 {{ $addr->recipient_phone }}</span>
                                </span>
                            </label>
                        @endforeach
                        @if($addresses->count() > 0)
                            <label class="p-4 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 has-[:checked]:border-brand-600 has-[:checked]:border-solid has-[:checked]:bg-brand-50/50 dark:has-[:checked]:bg-brand-500/10 cursor-pointer transition-colors flex items-center gap-3">
                                <input type="radio" name="selected_address_id" value="" id="addrNewRadio" {{ $useNew ? 'checked' : '' }} class="w-4 h-4 accent-emerald-600 shrink-0" data-addr-mode="new">
                                <span class="flex items-center gap-2 text-[13px] font-bold text-slate-800 dark:text-slate-100"><i class="ph-bold ph-plus-circle text-brand-600 text-lg"></i>{{ $gu ? 'નવું સરનામું દાખલ કરો / પિન કરો' : 'Deliver to a new address' }}</span>
                            </label>
                        @endif
                    </div>

                    <!-- Manual / map-filled address -->
                    <div id="manualAddressFields" class="{{ $useNew ? '' : 'hidden' }} {{ $addresses->count() ? 'mt-5 pt-5 border-t border-slate-100 dark:border-slate-800' : '' }}">
                        <input type="hidden" id="addrLat" name="latitude" value="{{ old('latitude', '23.030357') }}">
                        <input type="hidden" id="addrLng" name="longitude" value="{{ old('longitude', '72.507542') }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="inputHouseNo" class="fx-label">{{ __('messages.house_no') }} <span class="text-rose-500">*</span></label>
                                <input type="text" id="inputHouseNo" name="house_no" value="{{ old('house_no') }}" placeholder="{{ __('messages.ph_house') }}" class="fx-input @error('house_no') is-invalid @enderror" data-new-required>
                                <p id="err_inputHouseNo" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1 hidden"><i class="ph-fill ph-warning-circle"></i><span></span></p>
                                @error('house_no')<p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="inputStreet" class="fx-label">{{ __('messages.street_address') }} <span class="text-rose-500">*</span></label>
                                <input type="text" id="inputStreet" name="street_address" value="{{ old('street_address') }}" placeholder="{{ __('messages.ph_street') }}" class="fx-input @error('street_address') is-invalid @enderror" data-new-required>
                                <p id="err_inputStreet" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1 hidden"><i class="ph-fill ph-warning-circle"></i><span></span></p>
                                @error('street_address')<p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="inputLandmark" class="fx-label">{{ __('messages.landmark') }}</label>
                                <input type="text" id="inputLandmark" name="landmark" value="{{ old('landmark') }}" placeholder="{{ __('messages.ph_landmark') }}" class="fx-input">
                            </div>
                            <div>
                                <label for="inputCity" class="fx-label">{{ __('messages.city') }} <span class="text-rose-500">*</span></label>
                                <input type="text" id="inputCity" name="city" value="{{ old('city', 'Ahmedabad') }}" class="fx-input @error('city') is-invalid @enderror" data-new-required>
                                <p id="err_inputCity" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1 hidden"><i class="ph-fill ph-warning-circle"></i><span></span></p>
                                @error('city')<p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="inputPincode" class="fx-label">{{ __('messages.pincode') }} <span class="text-rose-500">*</span></label>
                                <input type="text" id="inputPincode" name="pincode" value="{{ old('pincode', '380054') }}" pattern="[0-9]{6}" inputmode="numeric" maxlength="6" class="fx-input font-mono @error('pincode') is-invalid @enderror" data-new-required>
                                <p id="err_inputPincode" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1 hidden"><i class="ph-fill ph-warning-circle"></i><span></span></p>
                                @error('pincode')<p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i>{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="inputAddrType" class="fx-label">{{ __('messages.address_type') }}</label>
                                <select id="inputAddrType" name="address_type" class="fx-input">
                                    @foreach(['Home', 'Work', 'Other'] as $t)<option value="{{ $t }}" {{ old('address_type') === $t ? 'selected' : '' }}>{{ __('messages.address_types.' . $t) }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <p id="mapPinNote" class="mt-3 text-[12px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5"><i class="ph ph-map-pin text-rose-500"></i><span>{{ $gu ? 'ચોક્કસ ડિલિવરી માટે નકશા પર પિન કરો.' : 'Tip: pin your doorstep on the map for precise delivery.' }}</span></p>
                    </div>
                </section>

                <!-- 3. Payment -->
                <section class="fx-card p-4 sm:p-6">
                    <h3 class="{{ $stepTitle }} mb-4"><span class="{{ $stepNum }}">3</span>{{ __('messages.select_payment_method') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach([['cod', 'ph-money', __('messages.cash_on_delivery'), $gu ? 'ડિલિવરી વખતે રોકડ અથવા UPI થી ચૂકવો' : 'Pay by cash or UPI at your doorstep'], ['upi', 'ph-qr-code', __('messages.online_upi'), 'PhonePe · Google Pay · Paytm']] as [$val, $ic, $lbl, $sub])
                            <label class="p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 dark:has-[:checked]:bg-brand-500/10 cursor-pointer transition-colors flex items-center gap-3">
                                <input type="radio" name="payment_method" value="{{ $val }}" {{ old('payment_method', 'cod') === $val ? 'checked' : '' }} class="w-4 h-4 accent-emerald-600 shrink-0">
                                <span class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xl shrink-0"><i class="ph-duotone {{ $ic }}"></i></span>
                                <span class="min-w-0">
                                    <span class="block font-bold text-[13px] text-slate-900 dark:text-white">{{ $lbl }}</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ $sub }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <label for="orderNotes" class="fx-label">{{ $gu ? 'ખાસ ડિલિવરી સૂચના (વૈકલ્પિક)' : 'Delivery instructions (optional)' }}</label>
                        <textarea id="orderNotes" name="notes" rows="2" placeholder="{{ $gu ? 'દા.ત. બે વાર બેલ વગાડો' : 'e.g. Ring the bell twice, leave with security…' }}" class="fx-input">{{ old('notes') }}</textarea>
                    </div>
                </section>
            </div>

            <!-- Summary column -->
            <aside class="space-y-4 lg:sticky lg:top-36">
                <!-- Coupon -->
                <div class="fx-card p-4 sm:p-5" id="couponBox" data-subtotal="{{ $subtotal }}" data-delivery="{{ $deliveryCharge }}">
                    <h4 class="font-extrabold text-slate-900 dark:text-white text-[14px] flex items-center gap-2 mb-3"><i class="ph-fill ph-ticket text-amber-500 text-lg"></i>{{ __('messages.apply_coupon') }}</h4>
                    <div id="couponApplied" class="{{ $appliedCoupon ? 'flex' : 'hidden' }} items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-dashed border-emerald-300 dark:border-emerald-500/40">
                        <i class="ph-fill ph-seal-check text-emerald-600 text-xl"></i>
                        <div class="flex-1 min-w-0">
                            <p class="font-mono font-extrabold text-[13px] text-emerald-800 dark:text-emerald-300" id="couponAppliedCode">{{ $appliedCoupon }}</p>
                            <p class="text-[11px] text-emerald-700/80 dark:text-emerald-300/70">{{ $gu ? 'કૂપન લાગુ થયું' : 'Coupon applied' }}</p>
                        </div>
                        <button type="button" onclick="removeCouponCode(this)" class="fx-btn fx-btn-ghost fx-btn-sm !text-rose-600">{{ $gu ? 'દૂર કરો' : 'Remove' }}</button>
                    </div>
                    <div id="couponForm" class="{{ $appliedCoupon ? 'hidden' : '' }}">
                        <div class="flex items-center gap-2">
                            <label class="flex-1"><span class="sr-only">{{ __('messages.enter_coupon_code') }}</span>
                                <input type="text" id="couponCodeInput" value="{{ $appliedCoupon }}" placeholder="FRESH20" autocomplete="off" class="fx-input !py-2.5 font-mono font-bold uppercase" onkeydown="if(event.key==='Enter'){event.preventDefault();applyCouponCode(document.getElementById('couponApplyBtn'));}">
                            </label>
                            <button type="button" id="couponApplyBtn" onclick="applyCouponCode(this)" class="fx-btn fx-btn-dark !py-2.5">{{ __('messages.apply') }}</button>
                        </div>
                        <p id="couponMsg" class="mt-2 text-[12px] font-semibold text-slate-400">{{ $gu ? 'અજમાવો: FRESH20 અથવા WELCOME50' : 'Try code: FRESH20 or WELCOME50' }} · <a href="{{ route('pages.offers') }}" target="_blank" class="text-brand-700 dark:text-brand-400 hover:underline">{{ $gu ? 'બધી ઑફર' : 'All offers' }}</a></p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="fx-card p-4 sm:p-5">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-[15px] mb-3">{{ $gu ? 'ઓર્ડર સારાંશ' : 'Order summary' }}</h3>
                    <details class="group mb-4" {{ $cartItems->count() <= 3 ? 'open' : '' }}>
                        <summary class="list-none cursor-pointer flex items-center justify-between text-[13px] font-semibold text-slate-600 dark:text-slate-300">
                            <span>{{ $cartItems->sum('quantity') }} {{ $gu ? 'વસ્તુઓ' : 'items' }}</span>
                            <i class="ph-bold ph-caret-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <ul class="mt-3 space-y-2.5 max-h-60 overflow-y-auto pr-1">
                            @foreach($cartItems as $ci)
                                <li class="flex items-center gap-3">
                                    <span class="relative shrink-0">
                                        <img src="{{ $ci->product->thumbnail_url }}" alt="" class="w-11 h-11 rounded-lg object-cover bg-slate-100 dark:bg-slate-800">
                                        <span class="absolute -top-1.5 -right-1.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-slate-700 text-white text-[10px] font-bold flex items-center justify-center">{{ $ci->quantity }}</span>
                                    </span>
                                    <span class="flex-1 min-w-0 text-[12px] font-semibold text-slate-700 dark:text-slate-200 line-clamp-2">{{ $ci->product->localized_name }}</span>
                                    <span class="text-[12px] font-bold text-slate-900 dark:text-white">₹{{ number_format($ci->subtotal, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                    <dl class="space-y-2.5 text-[13px] pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300"><dt>{{ __('messages.item_total') }}</dt><dd class="font-bold text-slate-900 dark:text-white">₹{{ number_format($subtotal, 2) }}</dd></div>
                        <div id="discountRow" class="flex justify-between text-emerald-700 dark:text-emerald-400 font-semibold {{ $discount > 0 ? '' : 'hidden' }}"><dt>{{ __('messages.discount') }}</dt><dd id="discountVal" class="font-bold">-₹{{ number_format($discount, 2) }}</dd></div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300"><dt>{{ __('messages.delivery_fee') }}</dt>
                            <dd class="font-bold">@if($deliveryCharge == 0)<span class="text-emerald-600 dark:text-emerald-400 uppercase">{{ __('messages.free') }}</span>@else<span class="text-slate-900 dark:text-white">₹{{ number_format($deliveryCharge, 2) }}</span>@endif</dd></div>
                        <div class="flex justify-between items-baseline pt-3 border-t border-dashed border-slate-200 dark:border-slate-700">
                            <dt class="font-extrabold text-slate-900 dark:text-white">{{ __('messages.grand_total') }}</dt>
                            <dd id="grandTotalVal" class="text-xl font-extrabold text-slate-900 dark:text-white">₹{{ number_format($total, 2) }}</dd>
                        </div>
                    </dl>
                    <button type="submit" id="placeOrderBtn" class="hidden lg:flex fx-btn fx-btn-primary fx-btn-lg w-full mt-5">
                        <i class="ph-fill ph-lock-simple"></i><span>{{ __('messages.place_order') }}</span>
                    </button>
                    <p class="mt-3 text-[11px] text-slate-400 text-center">{{ $gu ? 'ઓર્ડર આપીને તમે અમારી શરતો સ્વીકારો છો.' : 'By placing the order you agree to our terms.' }}</p>
                </div>
            </aside>
        </div>

        <!-- Mobile place-order bar -->
        <div class="lg:hidden h-20"></div>
        <div class="lg:hidden fixed inset-x-0 z-40 fx-above-tabbar">
            <div class="mx-3 mb-2 p-2.5 pl-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur border border-slate-200 dark:border-slate-700 shadow-2xl flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.grand_total') }}</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight" data-grand-total-mirror>₹{{ number_format($total, 2) }}</p>
                </div>
                <button type="submit" class="fx-btn fx-btn-primary !h-11 shrink-0"><i class="ph-fill ph-lock-simple"></i><span>{{ __('messages.place_order') }}</span></button>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet map picker -->
<div id="mapPickerModal" class="fx-overlay" aria-hidden="true">
    <div class="fx-backdrop" onclick="closeMapModal()"></div>
    <div class="fx-panel fx-panel-center fx-sheet-mobile outline-none" style="--fx-modal-w: 720px" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
        <div class="flex items-center justify-between px-5 h-14 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <h3 id="mapModalTitle" class="font-extrabold text-slate-900 dark:text-white text-[15px] flex items-center gap-2"><i class="ph-fill ph-map-pin text-rose-500"></i>{{ $gu ? 'નકશા પર ડિલિવરી સ્થાન પિન કરો' : 'Pin your doorstep on the map' }}</h3>
            <button type="button" onclick="closeMapModal()" class="fx-icon-btn" aria-label="{{ __('messages.close') }}"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="p-4 sm:p-5 space-y-3 overflow-y-auto">
            <div class="flex items-center gap-2">
                <label class="flex-1 relative"><span class="sr-only">{{ __('messages.search') }}</span>
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="mapSearchInput" placeholder="{{ $gu ? 'વિસ્તાર, લેન્ડમાર્ક શોધો…' : 'Search locality, landmark or area…' }}" class="fx-input !py-2.5 !pl-9" onkeydown="if(event.key==='Enter'){event.preventDefault();searchLocation();}">
                </label>
                <button type="button" onclick="searchLocation()" class="fx-btn fx-btn-dark !py-2.5">{{ $gu ? 'શોધો' : 'Search' }}</button>
                <button type="button" onclick="useCurrentLocation()" class="fx-btn fx-btn-soft !py-2.5" title="{{ __('messages.use_current_location') }}"><i class="ph-bold ph-crosshair"></i><span class="hidden sm:inline">GPS</span></button>
            </div>
            <div id="mapPickerContainer" class="border border-slate-200 dark:border-slate-700"></div>
            <p class="text-[12px] text-slate-500 dark:text-slate-400">{{ __('messages.drag_pin_hint') }}</p>
        </div>
        <div class="px-4 sm:px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
            <div class="text-[12px] text-slate-600 dark:text-slate-300 truncate flex items-center gap-1.5 min-w-0"><i class="ph-fill ph-map-pin text-rose-500 shrink-0"></i><span id="selectedAddressPreview" class="truncate">Ahmedabad, Gujarat</span></div>
            <button type="button" onclick="confirmMapAddress()" class="fx-btn fx-btn-primary shrink-0">{{ $gu ? 'આ સ્થાન કન્ફર્મ કરો' : 'Confirm this location' }}</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $ckT = $gu
        ? [
            'enter' => 'કૃપા કરીને કૂપન કોડ દાખલ કરો',
            'invalid' => 'અમાન્ય કૂપન કોડ',
            'removed' => 'કૂપન દૂર કર્યું',
            'not_found' => 'નકશા પર સ્થાન મળ્યું નહીં',
            'gps' => 'GPS સ્થાન પિન થયું!',
            'gps_fail' => 'સ્થાન મેળવી શકાયું નહીં',
            'confirmed' => 'સ્થાન કન્ફર્મ થયું અને સરનામું અપડેટ થયું!',
            'err_name' => 'કૃપા કરીને પૂરું નામ દાખલ કરો.',
            'err_phone' => 'કૃપા કરીને માન્ય ૧૦ અંકનો મોબાઇલ નંબર દાખલ કરો.',
            'err_house' => 'કૃપા કરીને ફ્લેટ / મકાન / બિલ્ડિંગ નંબર દાખલ કરો.',
            'err_street' => 'કૃપા કરીને શેરી / વિસ્તાર / સોસાયટીનું નામ દાખલ કરો.',
            'err_city' => 'કૃપા કરીને શહેરનું નામ દાખલ કરો.',
            'err_pincode' => 'કૃપા કરીને ૬ અંકનો પિનકોડ દાખલ કરો.',
            'err_summary' => 'કૃપા કરીને જરૂરી વિગતો યોગ્ય રીતે ભરો.'
          ]
        : [
            'enter' => 'Please enter a coupon code',
            'invalid' => 'Invalid coupon code',
            'removed' => 'Coupon removed',
            'not_found' => 'Location not found on map',
            'gps' => 'GPS location pinned!',
            'gps_fail' => 'Could not get your location',
            'confirmed' => 'Map location confirmed & address updated!',
            'err_name' => 'Please enter your full name.',
            'err_phone' => 'Please enter a valid 10-digit mobile number.',
            'err_house' => 'Please enter House / Flat / Building No.',
            'err_street' => 'Please enter Street / Area / Locality.',
            'err_city' => 'Please enter City.',
            'err_pincode' => 'Please enter a valid 6-digit Pincode.',
            'err_summary' => 'Please fill all required delivery details.'
          ];
@endphp
<script>
    const CK_T = @json($ckT);

    // ---------- Client-Side Form Validation ----------
    $('#checkoutForm').on('submit', function (e) {
        let isValid = true;
        let firstInvalid = null;

        function markError(el, msg) {
            const $el = $(el);
            $el.addClass('is-invalid');
            const errId = 'err_' + $el.attr('id');
            const $err = $('#' + errId);
            if ($err.length) {
                $err.find('span').text(msg);
                $err.removeClass('hidden');
            }
            if (!firstInvalid) firstInvalid = $el;
            isValid = false;
        }

        // Reset previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('[id^="err_"]').addClass('hidden');

        // 1. Customer Name
        const name = ($('#customer_name').val() || '').trim();
        if (!name || name.length < 2) {
            markError('#customer_name', CK_T.err_name);
        }

        // 2. Customer Phone (10 digits)
        const phone = ($('#customer_phone').val() || '').trim();
        if (!phone || !/^[6-9][0-9]{9}$/.test(phone)) {
            markError('#customer_phone', CK_T.err_phone);
        }

        // 3. Delivery Address (if manual/new is active)
        const hasSavedSelected = $('input[name="selected_address_id"]:checked').length && $('input[name="selected_address_id"]:checked').val() !== '';
        if (!hasSavedSelected) {
            const house = ($('#inputHouseNo').val() || '').trim();
            if (!house) {
                markError('#inputHouseNo', CK_T.err_house);
            }

            const street = ($('#inputStreet').val() || '').trim();
            if (!street) {
                markError('#inputStreet', CK_T.err_street);
            }

            const city = ($('#inputCity').val() || '').trim();
            if (!city) {
                markError('#inputCity', CK_T.err_city);
            }

            const pincode = ($('#inputPincode').val() || '').trim();
            if (!pincode || !/^[0-9]{6}$/.test(pincode)) {
                markError('#inputPincode', CK_T.err_pincode);
            }
        }

        if (!isValid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (typeof toastr !== 'undefined') {
                toastr.error(CK_T.err_summary);
            }
            if (firstInvalid) {
                $('html, body').animate({
                    scrollTop: firstInvalid.offset().top - 120
                }, 350);
                firstInvalid.focus();
            }
            return false;
        }
    });

    // Real-time error removal on typing
    $(document).on('input change', 'input, select, textarea', function () {
        $(this).removeClass('is-invalid');
        $('#err_' + $(this).attr('id')).addClass('hidden');
    });

    // ---------- Address mode (saved vs new) ----------
    function syncAddressMode() {
        const isNew = !document.querySelector('input[name="selected_address_id"]:checked') || document.querySelector('input[name="selected_address_id"]:checked').value === '';
        $('#manualAddressFields').toggleClass('hidden', !isNew);
        $('[data-new-required]').prop('required', isNew);
    }
    $(document).on('change', 'input[name="selected_address_id"]', syncAddressMode);
    syncAddressMode();

    // ---------- Coupon ----------
    function setGrandTotal(text) { $('#grandTotalVal').text(text); $('[data-grand-total-mirror]').text(text); }

    function applyCouponCode(btn) {
        const code = ($('#couponCodeInput').val() || '').trim();
        if (!code) { toastr.warning(CK_T.enter); $('#couponCodeInput').focus(); return; }
        FX.busy(btn, true);
        $.ajax({ url: @json(route('checkout.coupon.apply')), type: 'POST', data: { coupon_code: code } })
            .done(function (res) {
                if (!res.success) return;
                $('#discountRow').removeClass('hidden');
                $('#discountVal').text('-' + res.discount_formatted);
                setGrandTotal(res.total_formatted);
                $('#couponAppliedCode').text(res.coupon_code || code.toUpperCase());
                $('#couponApplied').removeClass('hidden').addClass('flex');
                $('#couponForm').addClass('hidden');
                $('#couponMsg').removeClass('text-rose-600').addClass('text-slate-400');
                toastr.success(res.message);
            })
            .fail(function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || CK_T.invalid;
                $('#couponMsg').removeClass('text-slate-400').addClass('text-rose-600').text(msg);
                $('#couponCodeInput').addClass('is-invalid');
                toastr.error(msg);
            })
            .always(() => FX.busy(btn, false));
    }
    $('#couponCodeInput').on('input', function () { $(this).removeClass('is-invalid'); });

    function removeCouponCode(btn) {
        FX.busy(btn, true);
        $.post(@json(route('checkout.coupon.remove')))
            .done(function (res) {
                const box = document.getElementById('couponBox');
                const total = parseFloat(box.dataset.subtotal) + parseFloat(box.dataset.delivery);
                $('#discountRow').addClass('hidden');
                setGrandTotal(FX.money(total));
                $('#couponApplied').addClass('hidden').removeClass('flex');
                $('#couponForm').removeClass('hidden');
                $('#couponCodeInput').val('');
                toastr.info(res.message || CK_T.removed);
            })
            .fail(() => toastr.error(CK_T.invalid))
            .always(() => FX.busy(btn, false));
    }

    // ---------- Leaflet map picker ----------
    let leafletMap = null;
    let marker = null;
    let currentLat = parseFloat($('#addrLat').val()) || 23.030357;
    let currentLng = parseFloat($('#addrLng').val()) || 72.507542;

    function openMapModal() {
        // picking on the map means delivering to a new address
        const nr = document.getElementById('addrNewRadio');
        if (nr && !nr.checked) { nr.checked = true; syncAddressMode(); }
        FX.open('mapPickerModal');
        setTimeout(() => {
            if (typeof L === 'undefined') return;
            if (!leafletMap) {
                leafletMap = L.map('mapPickerContainer').setView([currentLat, currentLng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(leafletMap);
                marker = L.marker([currentLat, currentLng], { draggable: true }).addTo(leafletMap);
                marker.on('dragend', function () {
                    const pos = marker.getLatLng();
                    currentLat = pos.lat; currentLng = pos.lng;
                    reverseGeocode(pos.lat, pos.lng);
                });
                leafletMap.on('click', function (e) {
                    marker.setLatLng(e.latlng);
                    currentLat = e.latlng.lat; currentLng = e.latlng.lng;
                    reverseGeocode(e.latlng.lat, e.latlng.lng);
                });
            } else {
                leafletMap.invalidateSize();
            }
        }, 260);
    }

    function closeMapModal() { FX.close('mapPickerModal'); }

    function searchLocation() {
        const query = $('#mapSearchInput').val();
        if (!query || !leafletMap) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Gujarat')}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat), lon = parseFloat(data[0].lon);
                    currentLat = lat; currentLng = lon;
                    leafletMap.setView([lat, lon], 15);
                    marker.setLatLng([lat, lon]);
                    reverseGeocode(lat, lon);
                } else {
                    toastr.warning(CK_T.not_found);
                }
            }).catch(() => toastr.warning(CK_T.not_found));
    }

    function useCurrentLocation() {
        if (!navigator.geolocation || !leafletMap) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
            currentLat = pos.coords.latitude; currentLng = pos.coords.longitude;
            leafletMap.setView([currentLat, currentLng], 16);
            marker.setLatLng([currentLat, currentLng]);
            reverseGeocode(currentLat, currentLng);
            toastr.success(CK_T.gps);
        }, () => toastr.error(CK_T.gps_fail));
    }

    function reverseGeocode(lat, lng) {
        $('#selectedAddressPreview').text(lat.toFixed(5) + ', ' + lng.toFixed(5));
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.address) {
                    const road = data.address.road || data.address.suburb || data.address.neighbourhood || '';
                    const city = data.address.city || data.address.town || data.address.state_district || 'Ahmedabad';
                    const pincode = data.address.postcode || '380054';
                    $('#selectedAddressPreview').text(`${road}, ${city} - ${pincode}`);
                    $('#inputStreet').val(road);
                    $('#inputCity').val(city);
                    $('#inputPincode').val(pincode);
                }
            }).catch(() => {});
    }

    function confirmMapAddress() {
        $('#addrLat').val(currentLat);
        $('#addrLng').val(currentLng);
        $('#mapPinNote span').text(Number(currentLat).toFixed(5) + ', ' + Number(currentLng).toFixed(5));
        toastr.success(CK_T.confirmed);
        closeMapModal();
        $('#inputHouseNo').trigger('focus');
    }
</script>
@endpush
