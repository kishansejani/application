@extends('frontend.layouts.app')

@section('title', __('messages.all_categories') . ' - ' . __('messages.store_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.all_categories') }}</h1>
        <p class="text-xs text-slate-500 mt-1">Browse our complete selection of fresh grocery departments and subcategories</p>
    </div>

    <div class="space-y-12">
        @foreach($categories as $category)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 border border-brand-100 overflow-hidden flex items-center justify-center text-2xl text-brand-600 shadow-sm shrink-0">
                            @if($category->image)
                                <img src="{{ $category->image_url }}" alt="{{ $category->localized_name }}" class="w-full h-full object-cover">
                            @else
                                <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-extrabold text-slate-900">
                                <a href="{{ route('categories.show', $category->slug) }}" class="hover:text-brand-600">
                                    {{ $category->localized_name }}
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $category->localized_description }}</p>
                        </div>
                    </div>

                    <a href="{{ route('categories.show', $category->slug) }}" class="text-xs font-bold text-brand-700 hover:text-brand-800 flex items-center gap-1.5 shrink-0">
                        <span>View Category Products</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Subcategories Chips / List -->
                @if($category->subCategories->count() > 0)
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Sub Categories:</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            @foreach($category->subCategories as $sub)
                                <a href="{{ route('subcategories.show', $sub->slug) }}" class="p-3 bg-slate-50 hover:bg-brand-50 border border-slate-200 hover:border-brand-300 rounded-2xl flex items-center gap-3 transition-colors group">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-white shrink-0 border border-slate-200">
                                        <img src="{{ $sub->image_url }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <h5 class="text-xs font-bold text-slate-800 group-hover:text-brand-700 truncate">{{ $sub->localized_name }}</h5>
                                        <span class="text-[10px] text-slate-400">View items</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection
