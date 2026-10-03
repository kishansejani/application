@extends('admin.layouts.admin')

@section('title', 'Edit page')

@section('content')
@php
    $isActive = $errors->any() ? (bool) old('is_active') : (bool) $page->is_active;
    $err = 'text-xs font-semibold text-rose-600 mt-1';
@endphp
    <x-admin.page-header :title="'Edit ' . $page->title_en" subtitle="Update page content and search metadata in English and Gujarati." icon="file-text">
        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-outline"><i class="ph ph-arrow-square-out"></i> View page</a>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back</a>
    </x-admin.page-header>

    <form id="pageForm" action="{{ route('admin.pages.update', $page) }}" method="POST" class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-5 items-start">
        @csrf
        @method('PUT')

        <div class="space-y-5 min-w-0">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-text-aa"></i> Page title</h3>
                        <p class="card-subtitle">Shown as the page heading and in the browser tab.</p>
                    </div>
                </div>
                <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="title_en" class="form-label">Title (English) <span class="text-rose-500">*</span></label>
                        <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $page->title_en) }}" required class="form-control">
                        @error('title_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="title_gu" class="form-label">Title (ગુજરાતી) <span class="text-rose-500">*</span></label>
                        <input type="text" id="title_gu" name="title_gu" lang="gu" value="{{ old('title_gu', $page->title_gu) }}" required class="form-control">
                        @error('title_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-article"></i> Content <span class="text-rose-500">*</span></h3>
                        <p class="card-subtitle">HTML is allowed. Use Preview to check the formatting.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold" role="tablist" aria-label="Content language">
                            <button type="button" class="lang-tab px-3 py-1.5 rounded-lg" data-lang="en" role="tab">English</button>
                            <button type="button" class="lang-tab px-3 py-1.5 rounded-lg" data-lang="gu" role="tab">ગુજરાતી</button>
                        </div>
                        <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold" role="tablist" aria-label="Editor mode">
                            <button type="button" class="mode-tab px-3 py-1.5 rounded-lg inline-flex items-center gap-1" data-mode="write"><i class="ph ph-code"></i> Write</button>
                            <button type="button" class="mode-tab px-3 py-1.5 rounded-lg inline-flex items-center gap-1" data-mode="preview"><i class="ph ph-eye"></i> Preview</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @foreach(['en' => 'English', 'gu' => 'ગુજરાતી'] as $lang => $label)
                        <div class="lang-pane" data-lang="{{ $lang }}">
                            <label for="content_{{ $lang }}" class="sr-only">Content ({{ $label }})</label>
                            <textarea id="content_{{ $lang }}" name="content_{{ $lang }}" rows="18" required @if($lang === 'gu') lang="gu" @endif
                                class="form-control content-editor {{ $lang === 'en' ? 'font-mono !text-[12.5px]' : '' }}">{{ old('content_'.$lang, $page->{'content_'.$lang}) }}</textarea>
                            <div class="content-preview hidden min-h-[300px] max-h-[640px] overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 p-5 text-sm leading-relaxed text-slate-700 dark:text-slate-200
                                [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:mb-3 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-5 [&_h2]:mb-2 [&_h3]:text-base [&_h3]:font-bold [&_h3]:mt-4 [&_h3]:mb-1.5
                                [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:mb-3 [&_a]:text-emerald-600 [&_a]:underline [&_strong]:font-bold" @if($lang === 'gu') lang="gu" @endif></div>
                            <p class="form-hint flex justify-between gap-3"><span class="word-count" data-for="content_{{ $lang }}"></span><span>{{ $label }}</span></p>
                            @error('content_'.$lang)<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-magnifying-glass"></i> Search engine listing</h3>
                        <p class="card-subtitle">Optional. Falls back to the page title when empty.</p>
                    </div>
                </div>
                <div class="card-body space-y-5">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Preview</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 truncate">{{ url('/page/'.$page->slug) }}</p>
                        <p id="serpTitle" class="text-[17px] text-blue-700 dark:text-blue-400 font-medium leading-snug truncate"></p>
                        <p id="serpDesc" class="text-[13px] text-slate-600 dark:text-slate-400 line-clamp-2"></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="meta_title_en" class="form-label flex justify-between">Meta title (English) <span class="char-count font-semibold text-slate-400" data-for="meta_title_en" data-max="60"></span></label>
                            <input type="text" id="meta_title_en" name="meta_title_en" value="{{ old('meta_title_en', $page->meta_title_en) }}" class="form-control seo-field">
                            @error('meta_title_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="meta_title_gu" class="form-label flex justify-between">Meta title (ગુજરાતી) <span class="char-count font-semibold text-slate-400" data-for="meta_title_gu" data-max="60"></span></label>
                            <input type="text" id="meta_title_gu" name="meta_title_gu" lang="gu" value="{{ old('meta_title_gu', $page->meta_title_gu) }}" class="form-control">
                            @error('meta_title_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="meta_description_en" class="form-label flex justify-between">Meta description (English) <span class="char-count font-semibold text-slate-400" data-for="meta_description_en" data-max="160"></span></label>
                            <textarea id="meta_description_en" name="meta_description_en" rows="3" class="form-control seo-field !min-h-[84px]">{{ old('meta_description_en', $page->meta_description_en) }}</textarea>
                            @error('meta_description_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="meta_description_gu" class="form-label flex justify-between">Meta description (ગુજરાતી) <span class="char-count font-semibold text-slate-400" data-for="meta_description_gu" data-max="160"></span></label>
                            <textarea id="meta_description_gu" name="meta_description_gu" lang="gu" rows="3" class="form-control !min-h-[84px]">{{ old('meta_description_gu', $page->meta_description_gu) }}</textarea>
                            @error('meta_description_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-5 min-w-0 xl:sticky xl:top-[84px]">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Publishing</h3>
                </div>
                <div class="card-body space-y-4">
                    @include('admin.categories._toggle', ['name' => 'is_active', 'checked' => $isActive, 'label' => 'Published', 'hint' => 'Visible to customers on the website.'])
                    <dl class="text-[12.5px] space-y-2">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-400">URL</dt><dd class="font-mono text-slate-700 dark:text-slate-200 truncate">/page/{{ $page->slug }}</dd></div>
                        @if($page->updated_at)
                            <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-400">Last updated</dt><dd class="font-semibold text-slate-700 dark:text-slate-200" title="{{ $page->updated_at->format('d M Y, h:i A') }}">{{ $page->updated_at->diffForHumans() }}</dd></div>
                        @endif
                    </dl>
                </div>
                <div class="hidden xl:flex items-center gap-2.5 px-5 pb-5">
                    <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> Update page</button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </div>
        </aside>

        <div class="xl:hidden sticky bottom-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-t border-slate-200 dark:border-slate-700 flex items-center gap-2.5">
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> Update page</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $(function () {
        const ON = 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white', OFF = 'text-slate-500 dark:text-slate-400';
        let lang = 'en', mode = 'write';

        function render() {
            $('.lang-tab').each(function () { const on = this.dataset.lang === lang; $(this).toggleClass(ON, on).toggleClass(OFF, !on).attr('aria-selected', on); });
            $('.mode-tab').each(function () { const on = this.dataset.mode === mode; $(this).toggleClass(ON, on).toggleClass(OFF, !on).attr('aria-selected', on); });
            $('.lang-pane').each(function () {
                const $p = $(this), active = this.dataset.lang === lang;
                $p.toggleClass('hidden', !active);
                if (!active) return;
                $p.find('.content-editor').toggleClass('hidden', mode !== 'write');
                const $prev = $p.find('.content-preview').toggleClass('hidden', mode !== 'preview');
                if (mode === 'preview') {
                    const html = $p.find('.content-editor').val();
                    // Render without scripts / inline handlers
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    doc.querySelectorAll('script, iframe, object, embed, style').forEach(n => n.remove());
                    doc.querySelectorAll('*').forEach(n => [...n.attributes].forEach(a => { if (/^on/i.test(a.name) || /javascript:/i.test(a.value)) n.removeAttribute(a.name); }));
                    $prev.html(doc.body.innerHTML.trim() || '<p class="text-slate-400">Nothing to preview yet.</p>');
                }
            });
        }
        $('.lang-tab').on('click', function () { lang = this.dataset.lang; render(); });
        $('.mode-tab').on('click', function () { mode = this.dataset.mode; render(); });

        // Show the pane containing an invalid (empty required) field instead of failing silently
        $('.content-editor').on('invalid', function () { lang = this.id.replace('content_', ''); mode = 'write'; render(); });

        // Word / character counters
        function words() { $('.word-count').each(function () { const t = $('<div>').html($('#' + this.dataset.for).val()).text().trim(); const n = t ? t.split(/\s+/).length : 0; $(this).text(n.toLocaleString() + (n === 1 ? ' word' : ' words')); }); }
        function chars() {
            $('.char-count').each(function () {
                const n = ($('#' + this.dataset.for).val() || '').length, max = +this.dataset.max;
                $(this).text(n + ' / ' + max).toggleClass('!text-rose-600', n > max).toggleClass('!text-emerald-600', n > 0 && n <= max);
            });
        }
        function serp() {
            $('#serpTitle').text($.trim($('#meta_title_en').val()) || $.trim($('#title_en').val()));
            const d = $.trim($('#meta_description_en').val()) || $('<div>').html($('#content_en').val()).text().replace(/\s+/g, ' ').trim().slice(0, 160);
            $('#serpDesc').text(d);
        }
        $('.content-editor').on('input', words);
        $('#meta_title_en, #meta_title_gu, #meta_description_en, #meta_description_gu').on('input', chars);
        $('#meta_title_en, #meta_description_en, #title_en, #content_en').on('input', serp);
        render(); words(); chars(); serp();
    });
</script>
@endpush
