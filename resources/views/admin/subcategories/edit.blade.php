@extends('admin.layouts.admin')

@section('title', 'Edit Sub Category')
@section('page-title', 'Edit Sub Category: ' . $subcategory->name_en)
@section('page-subtitle', 'Update subcategory assignments, bilingual titles, and images')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.subcategories.update', $subcategory) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Parent Category -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Parent Category <span class="text-rose-500">*</span>
                </label>
                <select name="category_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $subcategory->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_en }} ({{ $cat->name_gu }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- English Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Sub Category Name (English) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name_en" value="{{ old('name_en', $subcategory->name_en) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Name -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">
                    Sub Category Name (ગુજરાતી) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name_gu" value="{{ old('name_gu', $subcategory->name_gu) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Slug -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">URL Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $subcategory->slug) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Sort Order -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Display Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $subcategory->sort_order) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Drag & Drop Image Upload -->
        <div class="pt-4 border-t border-slate-100">
            @if($subcategory->image)
                <div class="mb-4 flex items-center gap-4">
                    <div class="w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                        <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name_en }}" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-700">Current Subcategory Image</p>
                        <p class="text-xs text-slate-400">Upload below to replace</p>
                    </div>
                </div>
            @endif

            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                Replace Sub Category Image (Drag & Drop or Browse)
            </label>
            <div id="subDropzone" class="dropzone-container p-6 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                <input type="file" id="subFileInput" name="image_file" accept="image/*" class="hidden">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700">Drop subcategory image here, or <span class="text-brand-600 underline">browse</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">Supports PNG, JPG, WEBP, SVG</p>
                    </div>
                </div>
                <div id="subPreview" class="mt-4 flex justify-center"></div>
            </div>
            <div class="mt-2 text-center text-xs text-slate-400 font-medium">OR Image URL:</div>
            <input type="url" name="image_url" value="{{ old('image_url', filter_var($subcategory->image, FILTER_VALIDATE_URL) ? $subcategory->image : '') }}" placeholder="https://..." class="mt-1.5 w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- English Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (English)</label>
                <textarea name="description_en" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_en', $subcategory->description_en) }}</textarea>
            </div>

            <!-- Gujarati Description -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Description (ગુજરાતી)</label>
                <textarea name="description_gu" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_gu', $subcategory->description_gu) }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-6 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $subcategory->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Active</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.subcategories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">Update Sub Category</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropArea = document.getElementById('subDropzone');
        const fileInput = document.getElementById('subFileInput');
        dropArea.addEventListener('click', () => fileInput.click());
        initDragAndDropUploader('subDropzone', 'subFileInput', 'subPreview');
    });
</script>
@endpush
