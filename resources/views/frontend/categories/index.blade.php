@extends('frontend.layouts.app')

@section('title', __('messages.all_categories') . ' - ' . __('messages.store_name'))

@section('content')
@php $gu = app()->getLocale() === 'gu'; @endphp
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8">

    <div class="mb-5 sm:mb-7">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('messages.all_categories') }}</h1>
        <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-1">{{ $gu ? 'તાજી કરિયાણાના તમામ વિભાગો અને પેટા શ્રેણીઓ' : 'Browse every fresh grocery department and subcategory' }}</p>
    </div>

    <!-- Quick jump chips -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 mb-5 -mx-3 px-3 sm:mx-0 sm:px-0">
        @foreach($categories as $category)
            <a href="#cat-{{ $category->slug }}" class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[12px] font-bold text-slate-700 dark:text-slate-200 hover:border-brand-400">
                <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }} text-brand-600 dark:text-brand-400"></i>{{ $category->localized_name }}
            </a>
        @endforeach
    </div>

    <div class="space-y-4 sm:space-y-5">
        @foreach($categories as $category)
            <section id="cat-{{ $category->slug }}" class="fx-card p-4 sm:p-6">
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('categories.show', $category->slug) }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-brand-50 dark:bg-brand-500/10 overflow-hidden flex items-center justify-center text-2xl text-brand-600 dark:text-brand-400 shrink-0">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <i class="{{ $category->icon ?: 'fa-solid fa-layer-group' }}"></i>
                        @endif
                    </a>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-[17px] sm:text-xl font-extrabold text-slate-900 dark:text-white leading-tight">
                            <a href="{{ route('categories.show', $category->slug) }}" class="hover:text-brand-700 dark:hover:text-brand-400">{{ $category->localized_name }}</a>
                        </h2>
                        @if($category->localized_description)
                            <p class="text-[12px] sm:text-[13px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ $category->localized_description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('categories.show', $category->slug) }}" class="hidden sm:inline-flex fx-btn fx-btn-soft fx-btn-sm shrink-0">{{ $gu ? 'બધું જુઓ' : 'Shop all' }}<i class="ph-bold ph-arrow-right"></i></a>
                    <a href="{{ route('categories.show', $category->slug) }}" class="sm:hidden fx-icon-btn shrink-0" aria-label="{{ $category->localized_name }}"><i class="ph-bold ph-caret-right"></i></a>
                </div>

                @if($category->subCategories->count() > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3">
                        @foreach($category->subCategories as $sub)
                            <a href="{{ route('subcategories.show', $sub->slug) }}" class="group p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-brand-50 dark:hover:bg-brand-500/10 border border-transparent hover:border-brand-200 dark:hover:border-brand-500/30 flex items-center gap-2.5 transition-colors">
                                <span class="w-10 h-10 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shrink-0">
                                    <img src="{{ $sub->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-[12px] font-bold text-slate-800 dark:text-slate-100 group-hover:text-brand-700 dark:group-hover:text-brand-300 line-clamp-2 leading-tight">{{ $sub->localized_name }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
    </div>
</div>
@endsection
