@extends('admin.layouts.admin')

@section('title', 'Edit Page: ' . $page->title_en)
@section('page-title', 'Edit Page: ' . $page->title_en)
@section('page-subtitle', 'Update HTML content and metadata in English and Gujarati')

@section('content')
<div class="max-w-5xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.pages.update', $page) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- English Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Page Title (English) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title_en" value="{{ old('title_en', $page->title_en) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Title -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">
                    Page Title (ગુજરાતી) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title_gu" value="{{ old('title_gu', $page->title_gu) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- English Content -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Content (English HTML / Text) <span class="text-rose-500">*</span>
                </label>
                <textarea name="content_en" rows="12" required class="w-full font-mono text-xs p-4 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('content_en', $page->content_en) }}</textarea>
            </div>

            <!-- Gujarati Content -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">
                    Content (ગુજરાતી HTML / લખાણ) <span class="text-rose-500">*</span>
                </label>
                <textarea name="content_gu" rows="12" required class="w-full text-xs p-4 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('content_gu', $page->content_gu) }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SEO Meta Title</label>
                <input type="text" name="meta_title_en" value="{{ old('meta_title_en', $page->meta_title_en) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">SEO Meta Title (GU)</label>
                <input type="text" name="meta_title_gu" value="{{ old('meta_title_gu', $page->meta_title_gu) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs">
            </div>
        </div>

        <div class="flex items-center gap-6 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $page->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Active & Published</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.pages.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">Update Page Content</button>
        </div>
    </form>
</div>
@endsection
