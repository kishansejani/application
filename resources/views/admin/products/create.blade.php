@extends('admin.layouts.admin')

@section('title', 'Add New Product')
@section('page-title', 'Add New Product')
@section('page-subtitle', 'Create product with multi-image gallery, bilingual content, pricing, and stock')

@section('content')
<div class="max-w-5xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Section 1: Basic Information -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-brand-600"></i>
                <span>General Information</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- English Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Product Name (English) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" required placeholder="e.g. Fresh Hybrid Tomatoes" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Gujarati Name -->
                <div>
                    <label class="block text-xs font-bold text-brand-700 uppercase mb-1">
                        Product Name (ગુજરાતી) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name_gu" value="{{ old('name_gu') }}" required placeholder="દા.ત. તાજા ટામેટા" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Category <span class="text-rose-500">*</span>
                    </label>
                    <select id="categorySelect" name="category_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="">-- Choose Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name_en }} ({{ $cat->name_gu }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sub Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub Category (Optional)</label>
                    <select id="subCategorySelect" name="sub_category_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="">-- Select Sub Category --</option>
                    </select>
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Packing / Unit <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="unit" value="{{ old('unit', '1 kg') }}" required placeholder="e.g. 1 kg, 500 g, 1 L, 1 Pack, 1 Dozen" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Item Code</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Auto-generated if empty (e.g. SKU-8X29Q)" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Section 2: Pricing & Stock Inventory -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-indian-rupee-sign text-emerald-600"></i>
                <span>Pricing & Inventory Management</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Regular MRP Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Regular Price (₹ MRP) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" required placeholder="0.00" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Discount Price -->
                <div>
                    <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">
                        Offer / Sale Price (₹)
                    </label>
                    <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price') }}" placeholder="Leave blank if no discount" class="w-full px-4 py-2.5 border border-emerald-200 bg-emerald-50/30 rounded-xl text-sm font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Stock Quantity -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Initial Stock Qty <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 50) }}" required min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Low Stock Threshold -->
                <div>
                    <label class="block text-xs font-bold text-amber-700 uppercase mb-1">
                        Low Stock Alert At
                    </label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" min="0" class="w-full px-4 py-2.5 border border-amber-200 bg-amber-50/30 rounded-xl text-sm font-bold text-amber-800 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Section 3: Media & Images (Drag & Drop Primary + Gallery) -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-images text-indigo-600"></i>
                <span>Product Media & Gallery</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Primary Thumbnail Upload -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Primary Thumbnail Image</label>
                    <div id="thumbDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                        <input type="file" id="thumbFileInput" name="thumbnail_file" accept="image/*" class="hidden">
                        <div class="flex flex-col items-center justify-center space-y-1.5">
                            <i class="fa-solid fa-image text-brand-600 text-2xl"></i>
                            <p class="text-xs font-bold text-slate-700">Drop primary image or <span class="text-brand-600 underline">browse</span></p>
                        </div>
                        <div id="thumbPreview" class="mt-3 flex justify-center"></div>
                    </div>
                    <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url') }}" placeholder="Or Thumbnail URL: https://..." class="mt-2 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Multi-Image Gallery Upload -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Additional Gallery Images (Multiple)</label>
                    <div id="galleryDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                        <input type="file" id="galleryFileInput" name="gallery_files[]" accept="image/*" multiple class="hidden">
                        <div class="flex flex-col items-center justify-center space-y-1.5">
                            <i class="fa-solid fa-photo-film text-indigo-600 text-2xl"></i>
                            <p class="text-xs font-bold text-slate-700">Drop multiple gallery pictures or <span class="text-indigo-600 underline">browse</span></p>
                        </div>
                        <div id="galleryPreview" class="mt-3 flex flex-wrap gap-2 justify-center"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Descriptions (Bilingual) -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-language text-purple-600"></i>
                <span>Bilingual Descriptions</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Short Description EN -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Short Tagline (English)</label>
                    <input type="text" name="short_description_en" value="{{ old('short_description_en') }}" placeholder="e.g. Farm fresh plump, juicy red hybrid tomatoes." class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Short Description GU -->
                <div>
                    <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Short Tagline (ગુજરાતી)</label>
                    <input type="text" name="short_description_gu" value="{{ old('short_description_gu') }}" placeholder="દા.ત. ખેતરમાંથી સીધા તાજા, લાલ અને રસદાર ટામેટા." class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Full Description EN -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Description (English)</label>
                    <textarea name="description_en" rows="4" placeholder="Detailed product specifications, benefits, origin..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_en') }}</textarea>
                </div>

                <!-- Full Description GU -->
                <div>
                    <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Full Description (ગુજરાતી)</label>
                    <textarea name="description_gu" rows="4" placeholder="પ્રોડક્ટની વિગતવાર માહિતી અને ફાયદા..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_gu') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 5: Status Flags -->
        <div class="flex items-center gap-8 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Feature on Storefront</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Publish Immediately (Active)</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition">Cancel</a>
            <button type="submit" class="px-7 py-3 btn-theme-primary font-bold rounded-xl text-xs shadow-md transition active:scale-95 flex items-center gap-2">
                <i class="fas fa-check"></i>
                <span>Save Product</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thumbnail upload
        const thumbDrop = document.getElementById('thumbDropzone');
        const thumbInput = document.getElementById('thumbFileInput');
        thumbDrop.addEventListener('click', () => thumbInput.click());
        initDragAndDropUploader('thumbDropzone', 'thumbFileInput', 'thumbPreview');

        // Gallery upload
        const galDrop = document.getElementById('galleryDropzone');
        const galInput = document.getElementById('galleryFileInput');
        galDrop.addEventListener('click', () => galInput.click());
        initDragAndDropUploader('galleryDropzone', 'galleryFileInput', 'galleryPreview');

        // Dynamic Subcategories
        $('#categorySelect').on('change', function() {
            const catId = $(this).val();
            const subSelect = $('#subCategorySelect');
            subSelect.html('<option value="">-- Loading Sub Categories --</option>');

            if (!catId) {
                subSelect.html('<option value="">-- Select Sub Category --</option>');
                return;
            }

            $.get(`/admin/categories/${catId}/subcategories`, function(data) {
                subSelect.html('<option value="">-- Select Sub Category --</option>');
                data.forEach(function(sub) {
                    subSelect.append(`<option value="${sub.id}">${sub.name_en} (${sub.name_gu})</option>`);
                });
            });
        });
    });
</script>
@endpush
