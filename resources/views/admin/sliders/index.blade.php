@extends('admin.layouts.admin')

@section('title', 'Manage Sliders')
@section('page-title', 'Homepage Banners & Sliders')
@section('page-subtitle', 'Manage promotional hero banners, badges, and links')

@section('action-buttons')
<a href="{{ route('admin.sliders.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
    <i class="fa-solid fa-plus"></i>
    <span>Add New Slider Banner</span>
</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="overflow-x-auto">
        <table id="slidersTable" class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <th class="p-3">Order</th>
                    <th class="p-3">Banner Preview</th>
                    <th class="p-3">Title (English / Gujarati)</th>
                    <th class="p-3">Badge & Link</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($sliders as $slider)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3 font-bold text-slate-400">#{{ $slider->sort_order }}</td>
                        <td class="p-3">
                            <div class="w-28 h-16 rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100">
                                <img src="{{ $slider->image_url }}" alt="Slider Banner" class="w-full h-full object-cover">
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-900 text-sm">{{ $slider->title_en ?: 'No English Title' }}</div>
                            <div class="text-xs text-brand-700 font-semibold mt-0.5">{{ $slider->title_gu ?: 'ગુજરાતી શીર્ષક નથી' }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">{{ $slider->subtitle_en }}</div>
                        </td>
                        <td class="p-3">
                            @if($slider->badge_en)
                                <span class="inline-block px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-[10px] mb-1">
                                    {{ $slider->badge_en }}
                                </span>
                            @endif
                            <div class="text-[11px] text-slate-500">
                                Type: <span class="font-semibold uppercase text-slate-700">{{ $slider->link_type }}</span>
                            </div>
                        </td>
                        <td class="p-3">
                            <button onclick="toggleSliderStatus({{ $slider->id }}, this)" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all {{ $slider->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $slider->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.sliders.edit', $slider) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="confirm-delete-btn p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No sliders found. Click "+ Add New Slider Banner" above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#slidersTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']]
        });
    });

    function toggleSliderStatus(id, btn) {
        $.ajax({
            url: `/admin/sliders/${id}/toggle-status`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_active) {
                        $(btn).removeClass('bg-slate-100 text-slate-500').addClass('bg-emerald-100 text-emerald-800').text('Active');
                        toastr.success('Slider activated');
                    } else {
                        $(btn).removeClass('bg-emerald-100 text-emerald-800').addClass('bg-slate-100 text-slate-500').text('Inactive');
                        toastr.info('Slider deactivated');
                    }
                }
            }
        });
    }
</script>
@endpush
