@extends('admin.layouts.admin')

@section('title', 'Content Pages')

@section('content')
    <x-admin.page-header title="Content pages" subtitle="About, privacy, terms and other static pages in English and Gujarati." icon="file-text">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline"><i class="ph ph-storefront"></i> Storefront</a>
    </x-admin.page-header>

    <div class="stat-grid cols-4">
        <x-admin.stat-card label="Total pages" :value="$stats['total'] ?? $pages->count()" icon="file-text" tone="slate" meta="Static content pages" />
        <x-admin.stat-card label="Published" :value="$stats['active'] ?? 0" icon="check-circle" tone="emerald" meta="Live on the website" />
        <x-admin.stat-card label="Drafts" :value="$stats['draft'] ?? 0" icon="pencil-ruler" tone="amber" meta="Not visible to customers" />
        <x-admin.stat-card label="Legal & policies" :value="$stats['policy'] ?? 0" icon="scales" tone="violet" meta="Terms and compliance" />
    </div>

    <div class="card table-card">
        <table id="pagesTable" class="w-full" data-export-title="Content pages">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>URL</th>
                    <th class="export-only">Meta title</th>
                    <th>Length</th>
                    <th>Last updated</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pages as $page)
                    @php $words = str_word_count(strip_tags((string) $page->content_en)); @endphp
                    <tr>
                        <td data-export="{{ $page->title_en }}{{ $page->title_gu ? ' / '.$page->title_gu : '' }}">
                            <div class="flex items-center gap-3 min-w-[13rem]">
                                <span class="stat-icon tone-{{ in_array($page->slug, ['privacy-policy', 'terms-and-conditions', 'return-policy', 'legal-information']) ? 'violet' : 'slate' }} shrink-0"><i class="ph-duotone {{ in_array($page->slug, ['privacy-policy', 'terms-and-conditions', 'return-policy', 'legal-information']) ? 'ph-scales' : 'ph-file-text' }}"></i></span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $page->title_en }}</a>
                                    @if($page->title_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" lang="gu">{{ $page->title_gu }}</span>@endif
                                </div>
                            </div>
                        </td>
                        <td data-export="{{ url('/page/'.$page->slug) }}">
                            <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="inline-flex items-center gap-1.5 font-mono text-[12px] text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline whitespace-nowrap">
                                /page/{{ $page->slug }} <i class="ph ph-arrow-square-out text-[11px]"></i>
                            </a>
                        </td>
                        <td>{{ $page->meta_title_en }}</td>
                        <td data-order="{{ $words }}" data-export="{{ $words }} words">
                            <span class="text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ number_format($words) }} {{ \Illuminate\Support\Str::plural('word', $words) }}</span>
                        </td>
                        <td data-order="{{ optional($page->updated_at)->timestamp }}" data-export="{{ optional($page->updated_at)->format('d M Y, h:i A') }}">
                            @if($page->updated_at)
                                <span class="block font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $page->updated_at->format('d M Y') }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $page->updated_at->diffForHumans() }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td data-order="{{ $page->is_active ? 1 : 0 }}" data-export="{{ $page->is_active ? 'Published' : 'Draft' }}">
                            @if($page->is_active)
                                <span class="badge badge-success badge-dot">Published</span>
                            @else
                                <span class="badge badge-warning badge-dot">Draft</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="act-btn" title="View on storefront"><i class="ph ph-arrow-square-out"></i></a>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#pagesTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 2, visible: false },           // Meta title: export-only
                { targets: [3], responsivePriority: 4 }
            ]
        });
    });
</script>
@endpush
