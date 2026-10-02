@extends('admin.layouts.admin')

@section('title', 'Create Slider Banner')
@section('page-title', 'Create New Slider Banner')
@section('page-subtitle', 'Upload high-resolution promotional banners with Gujarati & English text')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Drag and Drop Image Upload Zone -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                Slider Banner Image <span class="text-rose-500">*</span>
            </label>
            <div id="sliderDropzone" class="dropzone-container p-6 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                <input type="file" id="sliderFileInput" name="image_file" accept="image/*" class="hidden">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700">Drag and drop banner image here, or <span class="text-brand-600 underline">browse files</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">Supports PNG, JPG, WEBP up to 4MB (Recommended: 1200 x 500 px)</p>
                    </div>
                </div>
                <!-- Live Preview -->
                <div id="sliderPreview" class="mt-4 flex justify-center"></div>
            </div>
            <div class="mt-2 text-center text-xs text-slate-400 font-medium">OR provide an Image URL directly:</div>
            <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://images.unsplash.com/photo-..." class="mt-1.5 w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- English Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Title (English)</label>
                <input type="text" name="title_en" value="{{ old('title_en') }}" placeholder="e.g. Farm Fresh Fruits & Vegetables" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Title -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Title (ગુજરાતી)</label>
                <input type="text" name="title_gu" value="{{ old('title_gu') }}" placeholder="દા.ત. ખેતરમાંથી સીધા તાજા ફળો અને શાકભાજી" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- English Subtitle -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Subtitle (English)</label>
                <input type="text" name="subtitle_en" value="{{ old('subtitle_en') }}" placeholder="e.g. Express 2-Hour Delivery Before 12 PM" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Subtitle -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Subtitle (ગુજરાતી)</label>
                <input type="text" name="subtitle_gu" value="{{ old('subtitle_gu') }}" placeholder="દા.ત. બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં મેળવો" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Badge Text English -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Badge Text (English)</label>
                <input type="text" name="badge_en" value="{{ old('badge_en') }}" placeholder="e.g. 30% OFF Today" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Badge Text Gujarati -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Badge Text (ગુજરાતી)</label>
                <input type="text" name="badge_gu" value="{{ old('badge_gu') }}" placeholder="દા.ત. આજે ૩૦% સુધી છૂટ" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Action Link Type -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Link Type</label>
                <select name="link_type" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="none">No Link (Display Only)</option>
                    <option value="category">Open Category</option>
                    <option value="product">Open Product</option>
                    <option value="offer">Open Offer</option>
                    <option value="custom">Custom URL</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Display Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Publish Immediately (Active)</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.sliders.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">Save Slider Banner</button>
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
