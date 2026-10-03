@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('messages.pagination') }}" class="flex items-center justify-between gap-3">
        <p class="hidden sm:block text-[13px] text-slate-500 dark:text-slate-400">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}
        </p>
        <div class="flex items-center gap-1.5 mx-auto sm:mx-0">
            @php $pgBtn = 'min-w-[2.5rem] h-10 px-3 inline-flex items-center justify-center rounded-xl text-[13px] font-bold border transition-colors'; @endphp
            @if ($paginator->onFirstPage())
                <span class="{{ $pgBtn }} border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-600" aria-disabled="true"><i class="ph-bold ph-caret-left"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $pgBtn }} border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-brand-400" aria-label="{{ __('messages.previous') }}"><i class="ph-bold ph-caret-left"></i></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $pgBtn }} border-transparent text-slate-400">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="{{ $pgBtn }} border-brand-600 bg-brand-600 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $pgBtn }} border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-brand-400">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $pgBtn }} border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-brand-400" aria-label="{{ __('messages.next') }}"><i class="ph-bold ph-caret-right"></i></a>
            @else
                <span class="{{ $pgBtn }} border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-600" aria-disabled="true"><i class="ph-bold ph-caret-right"></i></span>
            @endif
        </div>
    </nav>
@endif
