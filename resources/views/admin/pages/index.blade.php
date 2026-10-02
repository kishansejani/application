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

    <!-- Top KPI / Pipeline Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Card 1: Total Pages -->
        <div class="group relative overflow-hidden rounded-2xl p-5 flex flex-col justify-between bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-lg border border-slate-700/60 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block text-slate-300">Total Pages</span>
                    <div class="text-3xl font-black mt-2 leading-none text-white">{{ $stats['total'] ?? $pages->count() }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/10 text-indigo-300 flex items-center justify-center text-sm shadow-inner border border-white/20 backdrop-blur-md flex-shrink-0">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-white/10 text-slate-200">● CMS Documents</span>
            </div>
            <i class="fa-solid fa-file-lines absolute -right-2 -bottom-2 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card 2: Published -->
        <div class="group relative overflow-hidden rounded-2xl p-5 flex flex-col justify-between bg-gradient-to-br from-white via-white to-emerald-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block text-emerald-700 dark:text-emerald-400">Live / Published</span>
                    <div class="text-3xl font-black mt-2 leading-none text-slate-900 dark:text-white">{{ $stats['active'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-sm shadow-md shadow-emerald-500/25 flex-shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Live on Web
                </span>
            </div>
            <i class="fa-solid fa-circle-check absolute -right-2 -bottom-2 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card 3: Drafts -->
        <div class="group relative overflow-hidden rounded-2xl p-5 flex flex-col justify-between bg-gradient-to-br from-white via-white to-amber-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block text-amber-700 dark:text-amber-400">Draft Pages</span>
                    <div class="text-3xl font-black mt-2 leading-none text-slate-900 dark:text-white">{{ $stats['draft'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-400 text-white flex items-center justify-center text-sm shadow-md shadow-amber-500/25 flex-shrink-0">
                    <i class="fa-solid fa-pen-ruler"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800">
                    ● Unpublished
                </span>
            </div>
            <i class="fa-solid fa-pen-ruler absolute -right-2 -bottom-2 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card 4: Legal & Policies -->
        <div class="group relative overflow-hidden rounded-2xl p-5 flex flex-col justify-between bg-gradient-to-br from-white via-white to-indigo-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block text-indigo-700 dark:text-indigo-400">Legal & Policies</span>
                    <div class="text-3xl font-black mt-2 leading-none text-slate-900 dark:text-white">{{ $stats['policy'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-blue-500 text-white flex items-center justify-center text-sm shadow-md shadow-indigo-500/25 flex-shrink-0">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800">
                    ★ Terms & Compliance
                </span>
            </div>
            <i class="fa-solid fa-scale-balanced absolute -right-2 -bottom-2 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3.5 rounded-l-2xl">Page Title</th>
                        <th class="px-4 py-3.5">Slug / URL Route</th>
                        <th class="px-4 py-3.5">Last Updated</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @foreach($pages as $page)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $page->title_en }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $page->title_gu }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-300">
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-xs font-semibold">
                                    <span>/page/{{ $page->slug }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 font-semibold">
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
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Edit Page">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
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
