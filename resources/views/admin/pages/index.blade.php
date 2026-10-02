@extends('admin.layouts.admin')

@section('title', 'Manage Static & Legal Pages')
@section('page-title', 'Manage CMS Pages')
@section('page-subtitle', 'Update content, policy guidelines, and legal terms in English and Gujarati')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <th class="p-4">Page Title (English / Gujarati)</th>
                    <th class="p-4">Slug / URL Route</th>
                    <th class="p-4">Last Updated</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @foreach($pages as $page)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $page->title_en }}</div>
                            <div class="text-xs text-brand-700 font-semibold mt-0.5">{{ $page->title_gu }}</div>
                        </td>
                        <td class="p-4 font-mono text-slate-600">
                            <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="hover:underline hover:text-brand-600 flex items-center gap-1">
                                <span>/page/{{ $page->slug }}</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        </td>
                        <td class="p-4 text-slate-500">
                            {{ $page->updated_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $page->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $page->is_active ? 'Active' : 'Draft' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View on Store">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit Content</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
