@extends('frontend.layouts.app')

@section('title', __('messages.saved_addresses') . ' - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <div class="flex items-end justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.saved_addresses') }}</h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'ઘર, ઓફિસ અને અન્ય ડિલિવરી સ્થાનો મેનેજ કરો' : 'Manage your home, office and other delivery locations' }}</p>
        </div>
        <button type="button" onclick="openNewAddressModal()" class="fx-btn fx-btn-primary shrink-0"><i class="ph-bold ph-plus"></i><span class="hidden xs:inline">{{ __('messages.add_new_address') }}</span></button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($addresses as $addr)
            <article class="fx-card p-4 sm:p-5 flex flex-col {{ $addr->is_default ? '!border-brand-500 ring-1 ring-brand-500/30' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-[12px]">
                        <i class="ph-fill {{ $addr->type === 'Home' ? 'ph-house' : ($addr->type === 'Work' ? 'ph-briefcase' : 'ph-map-pin') }} text-brand-600 dark:text-brand-400"></i>{{ \Lang::has('messages.address_types.' . $addr->type) ? __('messages.address_types.' . $addr->type) : $addr->type }}
                    </span>
                    @if($addr->is_default)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-brand-600 text-white font-extrabold text-[10px] uppercase tracking-wide"><i class="ph-fill ph-star"></i>{{ __('messages.default_address') }}</span>
                    @endif
                </div>
                <h3 class="mt-3 font-extrabold text-slate-900 dark:text-white text-[15px]">{{ $addr->recipient_name }}</h3>
                <p class="text-[12px] text-brand-700 dark:text-brand-400 font-mono font-bold">+91 {{ $addr->recipient_phone }}</p>
                <p class="mt-2 text-[13px] text-slate-600 dark:text-slate-300 leading-relaxed flex-1">{{ $addr->full_address }}</p>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    @if(!$addr->is_default)
                        <form action="{{ route('addresses.set_default', $addr) }}" method="POST" data-loading>
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="fx-btn fx-btn-ghost fx-btn-sm !text-brand-700 dark:!text-brand-400 !px-2"><i class="ph ph-star text-base"></i>{{ __('messages.set_as_default') }}</button>
                        </form>
                    @else
                        <span class="text-[12px] font-semibold text-slate-400 px-2">{{ $gu ? 'મુખ્ય ડિલિવરી સરનામું' : 'Primary delivery address' }}</span>
                    @endif

                    <div class="flex items-center gap-1">
                        @if($addr->latitude && $addr->longitude)
                            <a href="https://www.google.com/maps?q={{ $addr->latitude }},{{ $addr->longitude }}" target="_blank" rel="noopener" class="fx-icon-btn !w-9 !h-9" title="{{ $gu ? 'નકશા પર જુઓ' : 'View on map' }}" aria-label="{{ $gu ? 'નકશા પર જુઓ' : 'View on map' }}"><i class="ph ph-map-trifold text-lg"></i></a>
                        @endif
                        <button type="button" class="fx-icon-btn !w-9 !h-9" onclick="openEditAddressModal(this)" title="{{ $gu ? 'ફેરફાર કરો' : 'Edit' }}" aria-label="{{ $gu ? 'ફેરફાર કરો' : 'Edit' }}"
                                data-address="{{ json_encode(['action' => route('addresses.update', $addr), 'type' => $addr->type, 'recipient_name' => $addr->recipient_name, 'recipient_phone' => $addr->recipient_phone, 'house_no' => $addr->house_no, 'street_address' => $addr->street_address, 'landmark' => $addr->landmark, 'city' => $addr->city, 'pincode' => $addr->pincode, 'latitude' => $addr->latitude, 'longitude' => $addr->longitude, 'is_default' => (bool) $addr->is_default]) }}">
                            <i class="ph ph-pencil-simple text-lg"></i>
                        </button>
                        <form action="{{ route('addresses.destroy', $addr) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="confirm-delete-btn fx-icon-btn !w-9 !h-9 hover:!text-rose-600 hover:!bg-rose-50 dark:hover:!bg-rose-500/10" data-confirm-title="{{ $gu ? 'આ સરનામું કાઢી નાખવું છે?' : 'Delete this address?' }}" title="{{ $gu ? 'કાઢી નાખો' : 'Delete' }}" aria-label="{{ $gu ? 'કાઢી નાખો' : 'Delete' }}">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full fx-card py-14 px-6 text-center">
                <span class="w-20 h-20 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-500 flex items-center justify-center text-4xl mx-auto mb-4"><i class="ph-duotone ph-map-pin"></i></span>
                <h4 class="font-extrabold text-slate-900 dark:text-white">{{ $gu ? 'કોઈ સાચવેલું સરનામું નથી' : 'No saved addresses yet' }}</h4>
                <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'ઝડપી ચેકઆઉટ માટે તમારું ઘર અથવા ઓફિસ સરનામું ઉમેરો.' : 'Add your home or office address for one-tap checkout.' }}</p>
                <button type="button" onclick="openNewAddressModal()" class="fx-btn fx-btn-primary mt-5"><i class="ph-bold ph-plus"></i>{{ __('messages.add_new_address') }}</button>
            </div>
        @endforelse
    </div>
</div>

<!-- Add / edit address modal with Leaflet map picker -->
<div id="newAddressModal" class="fx-overlay" aria-hidden="true">
    <div class="fx-backdrop" onclick="closeNewAddressModal()"></div>
    <div class="fx-panel fx-panel-center fx-sheet-mobile outline-none" style="--fx-modal-w: 720px" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="addrModalTitle">
        <div class="flex items-center justify-between px-5 h-14 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <h3 id="addrModalTitle" class="font-extrabold text-slate-900 dark:text-white text-[15px] flex items-center gap-2"><i class="ph-fill ph-map-trifold text-brand-600"></i><span data-title-new>{{ __('messages.add_new_address') }}</span><span data-title-edit class="hidden">{{ $gu ? 'સરનામું બદલો' : 'Edit address' }}</span></h3>
            <button type="button" onclick="closeNewAddressModal()" class="fx-icon-btn" aria-label="{{ __('messages.close') }}"><i class="ph ph-x text-xl"></i></button>
        </div>

        <form id="addressForm" action="{{ route('addresses.store') }}" method="POST" class="flex flex-col min-h-0 flex-1" data-loading>
            @csrf
            <input type="hidden" name="_method" value="PUT" id="addrMethod" disabled>
            <div class="overflow-y-auto p-4 sm:p-5 space-y-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <label class="relative flex-1"><span class="sr-only">{{ __('messages.search') }}</span>
                            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="modalMapSearch" placeholder="{{ $gu ? 'વિસ્તાર, લેન્ડમાર્ક શોધો…' : 'Search locality, landmark…' }}" class="fx-input !py-2.5 !pl-9" onkeydown="if(event.key==='Enter'){event.preventDefault();searchModalLocation();}">
                        </label>
                        <button type="button" onclick="searchModalLocation()" class="fx-btn fx-btn-dark !py-2.5">{{ $gu ? 'શોધો' : 'Search' }}</button>
                    </div>
                    <div id="modalMapPickerContainer" class="w-full h-56 sm:h-60 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800"></div>
                    <p class="text-[11px] text-slate-400 mt-1.5">{{ $gu ? 'સરનામું આપમેળે ભરવા માટે પિન ખેંચો.' : 'Drag the pin to auto-fill the address fields below.' }}</p>
                </div>

                <input type="hidden" id="modalLat" name="latitude" value="23.030357">
                <input type="hidden" id="modalLng" name="longitude" value="72.507542">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="mRecipient" class="fx-label">{{ $gu ? 'પ્રાપ્તકર્તાનું નામ' : 'Recipient name' }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="mRecipient" name="recipient_name" value="{{ Auth::user()?->name }}" required class="fx-input">
                    </div>
                    <div>
                        <label for="mPhone" class="fx-label">{{ $gu ? 'સંપર્ક ફોન' : 'Contact phone' }} <span class="text-rose-500">*</span></label>
                        <input type="tel" id="mPhone" name="recipient_phone" value="{{ Auth::user()?->phone }}" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" class="fx-input font-mono">
                    </div>
                    <div>
                        <label for="modalHouseNo" class="fx-label">{{ __('messages.house_no') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="modalHouseNo" name="house_no" required placeholder="{{ $gu ? 'ફ્લેટ / ઘર / બિલ્ડિંગ' : 'Flat / house / building' }}" class="fx-input">
                    </div>
                    <div>
                        <label for="modalStreet" class="fx-label">{{ __('messages.street_address') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="modalStreet" name="street_address" required placeholder="{{ $gu ? 'શેરી / વિસ્તાર' : 'Street / area / locality' }}" class="fx-input">
                    </div>
                    <div>
                        <label for="mLandmark" class="fx-label">{{ __('messages.landmark') }}</label>
                        <input type="text" id="mLandmark" name="landmark" placeholder="{{ $gu ? 'મંદિર, બગીચા પાસે…' : 'Near temple, park, school…' }}" class="fx-input">
                    </div>
                    <div>
                        <label for="modalCity" class="fx-label">{{ __('messages.city') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="modalCity" name="city" value="Ahmedabad" required class="fx-input">
                    </div>
                    <div>
                        <label for="modalPincode" class="fx-label">{{ __('messages.pincode') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="modalPincode" name="pincode" value="380054" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric" class="fx-input font-mono">
                    </div>
                    <div>
                        <span class="fx-label">{{ __('messages.address_type') }}</span>
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach(['Home' => 'ph-house', 'Work' => 'ph-briefcase', 'Other' => 'ph-map-pin'] as $t => $ic)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $t }}" {{ $t === 'Home' ? 'checked' : '' }} class="peer sr-only">
                                    <span class="flex items-center justify-center gap-1 h-[2.6rem] rounded-xl border border-slate-300 dark:border-slate-600 text-[12px] font-bold text-slate-600 dark:text-slate-300 peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:text-brand-700 dark:peer-checked:bg-brand-500/10 dark:peer-checked:text-brand-300 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500"><i class="ph {{ $ic }}"></i>{{ __('messages.address_types.' . $t) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" id="defCheck" class="w-4 h-4 rounded accent-emerald-600">
                    <span class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">{{ __('messages.set_as_default') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 px-4 sm:px-5 py-3 border-t border-slate-100 dark:border-slate-800 shrink-0">
                <button type="button" onclick="closeNewAddressModal()" class="fx-btn fx-btn-outline">{{ $gu ? 'રદ કરો' : 'Cancel' }}</button>
                <button type="submit" class="fx-btn fx-btn-primary"><i class="ph-bold ph-floppy-disk"></i><span>{{ __('messages.save_address') }}</span></button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let modalLeafletMap = null;
    let modalMarker = null;
    const ADDR_STORE_URL = @json(route('addresses.store'));
    const ADDR_DEFAULTS = { recipient_name: @json(Auth::user()?->name), recipient_phone: @json(Auth::user()?->phone), city: 'Ahmedabad', pincode: '380054', latitude: 23.030357, longitude: 72.507542 };

    function showAddressModal(lat, lng) {
        FX.open('newAddressModal');
        setTimeout(() => {
            if (typeof L === 'undefined') return;
            if (!modalLeafletMap) {
                modalLeafletMap = L.map('modalMapPickerContainer').setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(modalLeafletMap);
                modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalLeafletMap);
                modalMarker.on('dragend', function () {
                    const pos = modalMarker.getLatLng();
                    $('#modalLat').val(pos.lat);
                    $('#modalLng').val(pos.lng);
                    reverseGeocodeModal(pos.lat, pos.lng);
                });
                modalLeafletMap.on('click', function (e) {
                    modalMarker.setLatLng(e.latlng);
                    $('#modalLat').val(e.latlng.lat);
                    $('#modalLng').val(e.latlng.lng);
                    reverseGeocodeModal(e.latlng.lat, e.latlng.lng);
                });
            } else {
                modalLeafletMap.invalidateSize();
                modalLeafletMap.setView([lat, lng], 14);
                modalMarker.setLatLng([lat, lng]);
            }
        }, 260);
    }

    function fillAddressForm(a) {
        const f = document.getElementById('addressForm');
        ['recipient_name', 'recipient_phone', 'house_no', 'street_address', 'landmark', 'city', 'pincode'].forEach(k => { f.elements[k].value = a[k] || ''; });
        $('#modalLat').val(a.latitude || ADDR_DEFAULTS.latitude);
        $('#modalLng').val(a.longitude || ADDR_DEFAULTS.longitude);
        $(f).find('input[name="type"][value="' + (a.type || 'Home') + '"]').prop('checked', true);
        $('#defCheck').prop('checked', !!a.is_default);
    }

    function openNewAddressModal() {
        const f = document.getElementById('addressForm');
        f.action = ADDR_STORE_URL;
        $('#addrMethod').prop('disabled', true);
        $('[data-title-new]').removeClass('hidden'); $('[data-title-edit]').addClass('hidden');
        fillAddressForm(Object.assign({}, ADDR_DEFAULTS, { type: 'Home' }));
        showAddressModal(ADDR_DEFAULTS.latitude, ADDR_DEFAULTS.longitude);
    }

    function openEditAddressModal(btn) {
        const a = JSON.parse(btn.getAttribute('data-address'));
        const f = document.getElementById('addressForm');
        f.action = a.action;
        $('#addrMethod').prop('disabled', false);
        $('[data-title-new]').addClass('hidden'); $('[data-title-edit]').removeClass('hidden');
        fillAddressForm(a);
        showAddressModal(parseFloat(a.latitude) || ADDR_DEFAULTS.latitude, parseFloat(a.longitude) || ADDR_DEFAULTS.longitude);
    }

    function closeNewAddressModal() { FX.close('newAddressModal'); }

    function searchModalLocation() {
        const query = $('#modalMapSearch').val();
        if (!query || !modalLeafletMap) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Gujarat')}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat), lon = parseFloat(data[0].lon);
                    modalLeafletMap.setView([lat, lon], 15);
                    modalMarker.setLatLng([lat, lon]);
                    $('#modalLat').val(lat);
                    $('#modalLng').val(lon);
                    reverseGeocodeModal(lat, lon);
                } else {
                    toastr.warning(@json($gu ? 'સ્થાન મળ્યું નહીં' : 'Location not found'));
                }
            }).catch(() => {});
    }

    function reverseGeocodeModal(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.address) {
                    const road = data.address.road || data.address.suburb || data.address.neighbourhood || '';
                    const city = data.address.city || data.address.town || 'Ahmedabad';
                    const pincode = data.address.postcode || '380054';
                    $('#modalStreet').val(road);
                    $('#modalCity').val(city);
                    $('#modalPincode').val(pincode);
                }
            }).catch(() => {});
    }
</script>
@endpush
