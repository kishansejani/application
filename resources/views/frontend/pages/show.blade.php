@extends('frontend.layouts.app')

@section('title', $page->localized_title . ' - ' . __('messages.store_name'))

@section('content')
@php
    $gu = app()->getLocale() === 'gu';
    $infoPages = [
        'about-us' => ['ph-info', __('messages.about_us')],
        'legal-information' => ['ph-scales', __('messages.legal_info')],
        'privacy-policy' => ['ph-shield-check', __('messages.privacy_policy')],
    ];
@endphp
<div class="border-b border-slate-200/70 dark:border-slate-800 bg-gradient-to-b from-brand-50/70 to-transparent dark:from-brand-500/5">
    <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-8 sm:py-12 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300 text-[11px] font-bold uppercase tracking-wider">
            <i class="ph-fill {{ $infoPages[$page->slug][0] ?? 'ph-file-text' }}"></i>FreshExpress
        </span>
        <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $page->localized_title }}</h1>
        <p class="text-[13px] text-slate-500 dark:text-slate-400 mt-2">{{ $gu ? 'છેલ્લે અપડેટ' : 'Last updated' }}: {{ $page->updated_at->format('d M, Y') }}</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10 grid grid-cols-1 md:grid-cols-4 gap-5 items-start">
    <nav class="md:sticky md:top-36 flex md:flex-col gap-2 overflow-x-auto no-scrollbar -mx-3 px-3 md:mx-0 md:px-0" aria-label="{{ $gu ? 'માહિતી પાનાં' : 'Information pages' }}">
        @foreach($infoPages as $slug => [$ic, $label])
            <a href="{{ route('pages.show', $slug) }}" class="shrink-0 flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-[13px] font-bold {{ $page->slug === $slug ? 'bg-brand-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-brand-400' }}">
                <i class="ph {{ $ic }} text-base"></i>{{ $label }}
            </a>
        @endforeach
    </nav>
    <article class="md:col-span-3 fx-card p-5 sm:p-8 fx-prose">
        @php $pc = (string) $page->localized_content; @endphp
        @if($pc !== strip_tags($pc))
            {{-- Admin-authored rich text: allow formatting tags only (no scripts / handlers) --}}
            {!! preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', strip_tags($pc, '<h1><h2><h3><h4><h5><p><br><strong><b><em><i><u><ul><ol><li><a><blockquote><span><div><table><thead><tbody><tr><th><td><hr><img>')) !!}
        @else
            {!! nl2br(e($pc)) !!}
        @endif
    </article>
</div>
@endsection
