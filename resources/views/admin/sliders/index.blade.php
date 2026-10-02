@extends('admin.layouts.admin')

@section('title', 'Sliders')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Homepage Sliders & Banners</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage promotional hero banners, sale badges, and link destinations.</p>
        </div>
        <a href="{{ route('admin.sliders.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Slider</span>
        </a>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="slidersTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Order</th>
                        <th class="px-4 py-3.5">Banner Preview</th>
                        <th class="px-4 py-3.5">Title & Info</th>
                        <th class="px-4 py-3.5">Badge & Type</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($sliders as $slider)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-400">
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded-lg text-[11px] text-slate-600 dark:text-slate-300">#{{ $slider->sort_order }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="w-28 h-16 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm bg-slate-100 dark:bg-slate-900 flex-shrink-0 group">
                                    <img src="{{ $slider->image_url }}" alt="Slider Banner" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $slider->title_en ?: 'No English Title' }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $slider->title_gu ?: 'ગુજરાતી શીર્ષક નથી' }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate max-w-xs">{{ $slider->subtitle_en }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($slider->badge_en)
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-[10px] mb-1 border border-amber-200 dark:border-amber-800">
                                        {{ $slider->badge_en }}
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Type: <span class="font-bold uppercase text-slate-700 dark:text-slate-300">{{ $slider->link_type }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <button onclick="toggleSliderStatus({{ $slider->id }}, this)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm border {{ $slider->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $slider->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span>{{ $slider->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.sliders.edit', $slider) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Edit Slider">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="confirm-delete-btn w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Delete Slider">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No sliders found. Click "+ Add New Slider" above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#slidersTable').DataTable({
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
                        $(btn).removeClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .addClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .html('<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span><span>Active</span>');
                        toastr.success('Slider banner activated');
                    } else {
                        $(btn).removeClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .addClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .html('<span class="w-2 h-2 rounded-full bg-slate-400"></span><span>Inactive</span>');
                        toastr.info('Slider banner deactivated');
                    }
                }
            }
        });
    }
</script>
@endpush
