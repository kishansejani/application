@extends('frontend.layouts.app')

@section('title', $page->title . ' - ' . config('app.name'))

@section('content')
<div class="bg-gradient-to-b from-primary-50/50 to-transparent dark:from-slate-800/40 py-10 border-b border-slate-100 dark:border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-primary-100 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 text-xs font-semibold uppercase tracking-wider mb-3">
            <i class="fas fa-file-alt"></i>
            {{ config('app.name') }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ $page->title }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
            {{ __('messages.last_updated') ?? 'Last updated' }}: {{ $page->updated_at->format('d M, Y') }}
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-10 border border-slate-100 dark:border-slate-700 shadow-sm prose prose-slate dark:prose-invert max-w-none text-slate-700 dark:text-slate-200 leading-relaxed">
        {!! nl2br(e($page->content)) !!}
    </div>

    <!-- Related Navigation Links -->
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('pages.show', 'about-us') }}" class="p-4 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl hover:border-primary-500 transition text-center group">
            <div class="text-primary-600 dark:text-primary-400 mb-1 group-hover:scale-110 transition duration-300"><i class="fas fa-info-circle text-lg"></i></div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('messages.about_us') }}</span>
        </a>
        <a href="{{ route('pages.show', 'legal-information') }}" class="p-4 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl hover:border-primary-500 transition text-center group">
            <div class="text-primary-600 dark:text-primary-400 mb-1 group-hover:scale-110 transition duration-300"><i class="fas fa-balance-scale text-lg"></i></div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('messages.legal_information') }}</span>
        </a>
        <a href="{{ route('pages.show', 'privacy-policy') }}" class="p-4 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl hover:border-primary-500 transition text-center group">
            <div class="text-primary-600 dark:text-primary-400 mb-1 group-hover:scale-110 transition duration-300"><i class="fas fa-user-shield text-lg"></i></div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('messages.privacy_policy') }}</span>
        </a>
    </div>
</div>
@endsection
