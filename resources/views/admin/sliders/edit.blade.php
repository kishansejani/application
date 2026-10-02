@extends('admin.layouts.admin')

@section('title', 'Edit Slider Banner')
@section('page-title', 'Edit Slider Banner')
@section('page-subtitle', 'Update banner content, media, and priority')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Current Banner & Replace Upload Zone -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Current Banner</label>
            <div class="mb-4 w-full max-w-lg h-44 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 shadow-sm">
                <img src="{{ $slider->image_url }}" alt="Banner" class="w-full h-full object-cover">
            </div>

            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                Replace Banner Image (Drag & Drop or Browse)
            </label>
            <div id="sliderDropzone" class="dropzone-container p-6 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                <input type="file" id="sliderFileInput" name="image_file" accept="image/*" class="hidden">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700">Drop new banner here, or <span class="text-brand-600 underline">browse</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">Leave blank to keep existing image</p>
                    </div>
                </div>
                <div id="sliderPreview" class="mt-4 flex justify-center"></div>
            </div>
            <div class="mt-2 text-center text-xs text-slate-400 font-medium">OR Image URL:</div>
            <input type="url" name="image_url" value="{{ old('image_url', filter_var($slider->image, FILTER_VALIDATE_URL) ? $slider->image : '') }}" placeholder="https://..." class="mt-1.5 w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- English Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Title (English)</label>
                <input type="text" name="title_en" value="{{ old('title_en', $slider->title_en) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Title -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Title (ગુજરાતી)</label>
                <input type="text" name="title_gu" value="{{ old('title_gu', $slider->title_gu) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- English Subtitle -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Subtitle (English)</label>
                <input type="text" name="subtitle_en" value="{{ old('subtitle_en', $slider->subtitle_en) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Subtitle -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Subtitle (ગુજરાતી)</label>
                <input type="text" name="subtitle_gu" value="{{ old('subtitle_gu', $slider->subtitle_gu) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Badge Text English -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Text (English)</label>
                <input type="text" name="badge_en" value="{{ old('badge_en', $slider->badge_en) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Badge Text Gujarati -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Badge Text (ગુજરાતી)</label>
                <input type="text" name="badge_gu" value="{{ old('badge_gu', $slider->badge_gu) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Action Link Type -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Link Type</label>
                <select name="link_type" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="none" {{ $slider->link_type === 'none' ? 'selected' : '' }}>No Link</option>
                    <option value="category" {{ $slider->link_type === 'category' ? 'selected' : '' }}>Open Category</option>
                    <option value="product" {{ $slider->link_type === 'product' ? 'selected' : '' }}>Open Product</option>
                    <option value="offer" {{ $slider->link_type === 'offer' ? 'selected' : '' }}>Open Offer</option>
                    <option value="custom" {{ $slider->link_type === 'custom' ? 'selected' : '' }}>Custom URL</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Display Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $slider->sort_order) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $slider->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Active & Visible on Store</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.sliders.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 btn-theme-primary font-bold rounded-xl text-xs shadow-md flex items-center gap-2 transition active:scale-95">
                <i class="fas fa-check"></i>
                <span>Update Slider Banner</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropArea = document.getElementById('sliderDropzone');
        const fileInput = document.getElementById('sliderFileInput');
        dropArea.addEventListener('click', () => fileInput.click());
        initDragAndDropUploader('sliderDropzone', 'sliderFileInput', 'sliderPreview');
    });
</script>
@endpush
