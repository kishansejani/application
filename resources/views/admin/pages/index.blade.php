@extends('admin.layouts.admin')

@section('title', 'Pages')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">CMS & Legal Pages</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage static pages, about us, privacy policy, and terms in English and Gujarati.</p>
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Page Title</th>
                        <th class="px-4 py-3.5">Slug / URL Route</th>
                        <th class="px-4 py-3.5">Last Updated</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @foreach($pages as $page)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $page->title_en }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $page->title_gu }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-300">
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-xs">
                                    <span>/page/{{ $page->slug }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
                                {{ $page->updated_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shadow-sm border {{ $page->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $page->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span>{{ $page->is_active ? 'Active' : 'Draft' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 flex items-center justify-center transition shadow-sm" title="View on Storefront">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-xl font-bold text-xs shadow-sm transition active:scale-95">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
