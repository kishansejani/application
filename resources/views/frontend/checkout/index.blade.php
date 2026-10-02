@extends('frontend.layouts.app')

@section('title', __('messages.proceed_to_checkout') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.proceed_to_checkout') }}</h1>
        <p class="text-xs text-slate-500 mt-0.5">Confirm your delivery location, choose timing slot, and place order</p>
    </div>

    <form id="checkoutForm" action="{{ route('checkout.place_order') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Left 2 Cols: Delivery Timing, Address Selector, Map Picker, Payment Method -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Delivery Timing Promise Banner (Rule: <12pm -> 2 hours vs Next Day) -->
                <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white shadow-lg space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400 text-slate-950 font-extrabold text-[10px] uppercase tracking-wider">
                            ⚡ {{ $deliverySlotInfo['type'] === 'two_hours' ? '2-HOUR EXPRESS DISPATCH' : 'NEXT-DAY MORNING DELIVERY' }}
                        </span>
                        <span class="text-xs text-emerald-200">Daily Cutoff: 12:00 PM</span>
                    </div>
                    <h3 class="text-lg font-extrabold">{{ app()->getLocale() === 'gu' ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</h3>
                    <p class="text-xs text-emerald-100">
                        {{ $deliverySlotInfo['type'] === 'two_hours' 
                            ? 'Your order is placed before 12:00 PM and will be delivered directly to your doorstep within 2 hours.'
                            : 'Your order is placed after 12:00 PM and is scheduled for tomorrow morning delivery between 9:00 AM - 12:00 PM.'
                        }}
                    </p>
                </div>

                <!-- 2. Customer Contact Details -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-user text-brand-600"></i>
                        <span>Customer Contact Information</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', Auth::user()?->name) }}" required placeholder="e.g. Jignesh Patel" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">10-Digit Mobile Number <span class="text-rose-500">*</span></label>
                            <input type="tel" name="customer_phone" value="{{ old('customer_phone', Auth::user()?->phone) }}" required pattern="[0-9]{10}" placeholder="9876543210" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- 3. Delivery Address Selector & Map Picker -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-rose-500"></i>
                            <span>{{ __('messages.select_delivery_address') }}</span>
                        </h3>
                        <button type="button" onclick="openMapModal()" class="px-3.5 py-1.5 bg-brand-50 hover:bg-brand-100 border border-brand-200 text-brand-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <span>{{ __('messages.pick_on_map') }}</span>
                        </button>
                    </div>

                    <!-- Saved Addresses Cards (if logged in) -->
                    @if($addresses->count() > 0)
                        <div class="space-y-3">
                            <label class="block text-xs font-bold uppercase text-slate-400">Choose from Saved Addresses:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($addresses as $addr)
                                    <label class="p-4 rounded-2xl border-2 border-slate-200 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/30 cursor-pointer transition-all flex items-start gap-3">
                                        <input type="radio" name="selected_address_id" value="{{ $addr->id }}" {{ ($defaultAddress && $defaultAddress->id == $addr->id) || $loop->first ? 'checked' : '' }} class="mt-1 text-brand-600 focus:ring-brand-500">
                                        <div class="text-xs space-y-1">
                                            <span class="font-bold text-slate-900 text-sm block">{{ $addr->type }} ({{ $addr->recipient_name }})</span>
                                            <p class="text-slate-600 leading-relaxed">{{ $addr->full_address }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">Ph: +91 {{ $addr->recipient_phone }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2 text-center text-xs text-slate-400 font-semibold">
                            --- OR ENTER / PIN NEW ADDRESS BELOW ---
                        </div>
                    @endif

                    <!-- Manual / Map Populated Address Inputs -->
                    <div id="manualAddressFields" class="space-y-4 pt-2">
                        <!-- Hidden Map Coordinates -->
                        <input type="hidden" id="addrLat" name="latitude" value="{{ old('latitude', '23.030357') }}">
                        <input type="hidden" id="addrLng" name="longitude" value="{{ old('longitude', '72.507542') }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.house_no') }}</label>
                                <input type="text" id="inputHouseNo" name="house_no" value="{{ old('house_no') }}" placeholder="e.g. Flat 402, Shivam Heights" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.street_address') }}</label>
                                <input type="text" id="inputStreet" name="street_address" value="{{ old('street_address') }}" placeholder="e.g. Near SG Highway, Bodakdev" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.landmark') }}</label>
                                <input type="text" id="inputLandmark" name="landmark" value="{{ old('landmark') }}" placeholder="e.g. Opp Iskcon Temple" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.city') }}</label>
                                <input type="text" id="inputCity" name="city" value="{{ old('city', 'Ahmedabad') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.pincode') }}</label>
                                <input type="text" id="inputPincode" name="pincode" value="{{ old('pincode', '380054') }}" pattern="[0-9]{6}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.address_type') }}</label>
                                <select name="address_type" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                    <option value="Home">Home</option>
                                    <option value="Work">Work</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Payment Method -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-credit-card text-emerald-600"></i>
                        <span>{{ __('messages.select_payment_method') }}</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="p-4 rounded-2xl border-2 border-slate-200 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/30 cursor-pointer transition-all flex items-center gap-3">
                            <input type="radio" name="payment_method" value="cod" checked class="text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ __('messages.cash_on_delivery') }}</span>
                                <span class="text-[11px] text-slate-500">Pay cash or UPI upon delivery at doorstep</span>
                            </div>
                        </label>

                        <label class="p-4 rounded-2xl border-2 border-slate-200 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/30 cursor-pointer transition-all flex items-center gap-3">
                            <input type="radio" name="payment_method" value="upi" class="text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ __('messages.online_upi') }}</span>
                                <span class="text-[11px] text-slate-500">Instant PhonePe / Google Pay / Paytm QR</span>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Special Delivery Instructions (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Ring bell twice, leave with security..." class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none"></textarea>
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Coupon Applicator, Cart Summary & Place Order Button -->
            <div class="space-y-6">

                <!-- Coupon Applicator Card -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-tag text-amber-500"></i>
                        <span>{{ __('messages.apply_coupon') }}</span>
                    </h4>

                    <div class="flex items-center gap-2">
                        <input type="text" id="couponCodeInput" value="{{ $appliedCoupon }}" placeholder="e.g. FRESH20, WELCOME50" class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <button type="button" onclick="applyCouponCode()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
                            {{ __('messages.apply') }}
                        </button>
                    </div>
                    <p id="couponMsg" class="text-xs font-semibold {{ $appliedCoupon ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $appliedCoupon ? "Coupon '{$appliedCoupon}' applied!" : "Try code: FRESH20 or WELCOME50" }}
                    </p>
                </div>

                <!-- Order Final Summary Card -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 sticky top-28">
                    <h3 class="font-extrabold text-slate-900 text-base pb-4 border-b border-slate-100">Review & Payment</h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('messages.item_total') }}</span>
                            <span class="font-bold text-slate-900">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div id="discountRow" class="flex justify-between text-emerald-600 font-bold {{ $discount > 0 ? '' : 'hidden' }}">
                            <span>{{ __('messages.discount') }}</span>
                            <span id="discountVal">-₹{{ number_format($discount, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('messages.delivery_fee') }}</span>
                            <span class="font-bold text-slate-900">
                                @if($deliveryCharge == 0)
                                    <span class="text-emerald-600 uppercase font-extrabold">{{ __('messages.free') }}</span>
                                @else
                                    <span>₹40.00</span>
                                @endif
                            </span>
                        </div>

                        <div class="flex justify-between text-slate-900 font-extrabold text-lg pt-4 border-t border-slate-200">
                            <span>{{ __('messages.grand_total') }}</span>
                            <span id="grandTotalVal" class="text-brand-700">₹{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-extrabold shadow-xl shadow-brand-500/25 transition-all text-center flex items-center justify-center gap-2">
                        <i class="fa-solid fa-lock"></i>
                        <span>{{ __('messages.place_order') }}</span>
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>

<!-- Interactive Leaflet Map Modal -->
<div id="mapPickerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div onclick="closeMapModal()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

    <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl z-10 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-map-pin text-rose-500"></i>
                <span>Pin Exact Delivery Doorstep on Map</span>
            </h3>
            <button onclick="closeMapModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <input type="text" id="mapSearchInput" placeholder="Search locality, landmark, or area..." class="flex-1 px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none">
            <button type="button" onclick="searchLocation()" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Search</button>
            <button type="button" onclick="useCurrentLocation()" class="px-3 py-2 bg-brand-50 text-brand-700 border border-brand-200 rounded-xl text-xs font-bold flex items-center gap-1">
                <i class="fa-solid fa-crosshairs"></i>
                <span>GPS</span>
            </button>
        </div>

        <!-- Leaflet Map Canvas -->
        <div id="mapPickerContainer" class="border border-slate-200"></div>

        <p class="text-[11px] text-slate-500 italic">
            {{ __('messages.drag_pin_hint') }}
        </p>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <div class="text-xs text-slate-600 truncate max-w-sm" id="selectedAddressPreview">
                Pin moved to: Ahmedabad, Gujarat
            </div>
            <button type="button" onclick="confirmMapAddress()" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">
                Confirm This Location
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Coupon Applicator
    function applyCouponCode() {
        const code = $('#couponCodeInput').val();
        if (!code) {
            toastr.warning('Please enter a coupon code');
            return;
        }

        $.ajax({
            url: '{{ route("checkout.coupon.apply") }}',
            type: 'POST',
            data: { coupon_code: code },
            success: function(res) {
                if (res.success) {
                    $('#discountRow').removeClass('hidden');
                    $('#discountVal').text('-' + res.discount_formatted);
                    $('#grandTotalVal').text(res.total_formatted);
                    $('#couponMsg').removeClass('text-slate-400 text-rose-600').addClass('text-emerald-600').text(res.message);
                    toastr.success(res.message);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Invalid coupon code';
                $('#couponMsg').removeClass('text-emerald-600 text-slate-400').addClass('text-rose-600').text(msg);
                toastr.error(msg);
            }
        });
    }

    // Leaflet Interactive Map Logic
    let leafletMap = null;
    let marker = null;
    let currentLat = 23.030357;
    let currentLng = 72.507542;

    function openMapModal() {
        $('#mapPickerModal').removeClass('hidden');
        setTimeout(() => {
            if (!leafletMap) {
                leafletMap = L.map('mapPickerContainer').setView([currentLat, currentLng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(leafletMap);

                marker = L.marker([currentLat, currentLng], { draggable: true }).addTo(leafletMap);

                marker.on('dragend', function (e) {
                    const pos = marker.getLatLng();
                    currentLat = pos.lat;
                    currentLng = pos.lng;
                    reverseGeocode(pos.lat, pos.lng);
                });

                leafletMap.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    currentLat = e.latlng.lat;
                    currentLng = e.latlng.lng;
                    reverseGeocode(e.latlng.lat, e.latlng.lng);
                });
            } else {
                leafletMap.invalidateSize();
            }
        }, 200);
    }

    function closeMapModal() {
        $('#mapPickerModal').addClass('hidden');
    }

    function searchLocation() {
        const query = $('#mapSearchInput').val();
        if (!query) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Gujarat')}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    currentLat = lat;
                    currentLng = lon;
                    leafletMap.setView([lat, lon], 15);
                    marker.setLatLng([lat, lon]);
                    reverseGeocode(lat, lon);
                } else {
                    toastr.warning('Location not found on map');
                }
            });
    }

    function useCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                currentLat = pos.coords.latitude;
                currentLng = pos.coords.longitude;
                leafletMap.setView([currentLat, currentLng], 16);
                marker.setLatLng([currentLat, currentLng]);
                reverseGeocode(currentLat, currentLng);
                toastr.success('GPS Location pinned!');
            });
        }
    }

    function reverseGeocode(lat, lng) {
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
            });
    }

    function confirmMapAddress() {
        $('#addrLat').val(currentLat);
        $('#addrLng').val(currentLng);
        toastr.success('Map location confirmed & address updated!');
        closeMapModal();
    }
</script>
@endpush
