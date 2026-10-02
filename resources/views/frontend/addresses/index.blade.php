@extends('frontend.layouts.app')

@section('title', __('messages.saved_addresses') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.saved_addresses') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage your home, office, and doorstep delivery GPS locations</p>
        </div>
        <button onclick="openNewAddressModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl text-xs font-bold shadow-md shadow-brand-500/20 transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>{{ __('messages.add_new_address') }}</span>
        </button>
    </div>

    <!-- Saved Addresses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($addresses as $addr)
            <div class="bg-white rounded-3xl border-2 {{ $addr->is_default ? 'border-brand-600 bg-brand-50/10' : 'border-slate-200' }} shadow-sm p-6 space-y-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-800 font-bold text-xs rounded-xl">
                            <i class="fa-solid {{ $addr->type === 'Home' ? 'fa-house' : ($addr->type === 'Work' ? 'fa-briefcase' : 'fa-location-dot') }} text-brand-600"></i>
                            <span>{{ $addr->type }}</span>
                        </span>

                        @if($addr->is_default)
                            <span class="px-2.5 py-0.5 bg-brand-600 text-white font-extrabold text-[10px] rounded-full uppercase">
                                {{ __('messages.default_address') }}
                            </span>
                        @endif
                    </div>

                    <h3 class="font-extrabold text-slate-900 text-sm">{{ $addr->recipient_name }}</h3>
                    <p class="text-xs text-brand-700 font-mono font-bold mt-0.5">+91 {{ $addr->recipient_phone }}</p>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">{{ $addr->full_address }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                    @if(!$addr->is_default)
                        <form action="{{ route('addresses.set_default', $addr) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-brand-600 hover:underline">
                                {{ __('messages.set_as_default') }}
                            </button>
                        </form>
                    @else
                        <span class="text-slate-400">Primary Delivery Address</span>
                    @endif

                    <div class="flex items-center gap-2">
                        @if($addr->latitude && $addr->longitude)
                            <a href="https://www.google.com/maps?q={{ $addr->latitude }},{{ $addr->longitude }}" target="_blank" class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-50" title="View on Map">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </a>
                        @endif
                        <form action="{{ route('addresses.destroy', $addr) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="confirm-delete-btn p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-4">
                <i class="fa-solid fa-location-dot text-5xl text-slate-300"></i>
                <h4 class="font-bold text-slate-700 text-base">No saved addresses found.</h4>
                <p class="text-xs text-slate-400">Add your home or office address for 1-click checkout.</p>
                <button onclick="openNewAddressModal()" class="px-6 py-3 bg-brand-600 text-white rounded-2xl text-xs font-bold shadow-md">
                    + Add New Address
                </button>
            </div>
        @endforelse
    </div>

</div>

<!-- New Address Modal with Leaflet Map Picker -->
<div id="newAddressModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div onclick="closeNewAddressModal()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

    <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl z-10 space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-brand-600"></i>
                <span>{{ __('messages.add_new_address') }}</span>
            </h3>
            <button onclick="closeNewAddressModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Map Picker Canvas inside modal -->
        <div>
            <div class="flex items-center gap-2 mb-2">
                <input type="text" id="modalMapSearch" placeholder="Search locality, landmark..." class="flex-1 px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none">
                <button type="button" onclick="searchModalLocation()" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Search</button>
            </div>
            <div id="modalMapPickerContainer" style="height: 220px; width: 100%; border-radius: 1rem;" class="border border-slate-200"></div>
            <p class="text-[10px] text-slate-400 mt-1 italic">Drag the red pin to auto-fill address fields below.</p>
        </div>

        <form action="{{ route('addresses.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" id="modalLat" name="latitude" value="23.030357">
            <input type="hidden" id="modalLng" name="longitude" value="72.507542">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Recipient Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="recipient_name" value="{{ Auth::user()?->name }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Contact Phone <span class="text-rose-500">*</span></label>
                    <input type="tel" name="recipient_phone" value="{{ Auth::user()?->phone }}" required pattern="[0-9]{10}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.house_no') }} <span class="text-rose-500">*</span></label>
                    <input type="text" id="modalHouseNo" name="house_no" required placeholder="Flat / House / Building" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.street_address') }} <span class="text-rose-500">*</span></label>
                    <input type="text" id="modalStreet" name="street_address" required placeholder="Street / Area / Locality" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.landmark') }}</label>
                    <input type="text" name="landmark" placeholder="Near temple, park, school..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.city') }} <span class="text-rose-500">*</span></label>
                    <input type="text" id="modalCity" name="city" value="Ahmedabad" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.pincode') }} <span class="text-rose-500">*</span></label>
                    <input type="text" id="modalPincode" name="pincode" value="380054" required pattern="[0-9]{6}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">{{ __('messages.address_type') }}</label>
                    <select name="type" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="Home">Home</option>
                        <option value="Work">Work</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_default" value="1" id="defCheck" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <label for="defCheck" class="text-xs font-semibold text-slate-700 cursor-pointer">{{ __('messages.set_as_default') }}</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeNewAddressModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">{{ __('messages.save_address') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let modalLeafletMap = null;
    let modalMarker = null;

    function openNewAddressModal() {
        $('#newAddressModal').removeClass('hidden');
        setTimeout(() => {
            if (!modalLeafletMap) {
                modalLeafletMap = L.map('modalMapPickerContainer').setView([23.030357, 72.507542], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(modalLeafletMap);

                modalMarker = L.marker([23.030357, 72.507542], { draggable: true }).addTo(modalLeafletMap);

                modalMarker.on('dragend', function (e) {
                    const pos = modalMarker.getLatLng();
                    $('#modalLat').val(pos.lat);
                    $('#modalLng').val(pos.lng);
                    reverseGeocodeModal(pos.lat, pos.lng);
                });

                modalLeafletMap.on('click', function(e) {
                    modalMarker.setLatLng(e.latlng);
                    $('#modalLat').val(e.latlng.lat);
                    $('#modalLng').val(e.latlng.lng);
                    reverseGeocodeModal(e.latlng.lat, e.latlng.lng);
                });
            } else {
                modalLeafletMap.invalidateSize();
            }
        }, 200);
    }

    function closeNewAddressModal() {
        $('#newAddressModal').addClass('hidden');
    }

    function searchModalLocation() {
        const query = $('#modalMapSearch').val();
        if (!query) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Gujarat')}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    modalLeafletMap.setView([lat, lon], 15);
                    modalMarker.setLatLng([lat, lon]);
                    $('#modalLat').val(lat);
                    $('#modalLng').val(lon);
                    reverseGeocodeModal(lat, lon);
                }
            });
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
            });
    }
</script>
@endpush
