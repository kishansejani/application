{{-- Switch-style checkbox row. Params: name, checked, label, hint (optional), value (default 1).
     A hidden "0" is sent when unchecked so controllers using $request->boolean(name, true) can still turn it off. --}}
<label class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition">
    <span class="min-w-0">
        <span class="block text-[13.5px] font-bold text-slate-800 dark:text-slate-100">{{ $label }}</span>
        @if(!empty($hint))<span class="block text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $hint }}</span>@endif
    </span>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="{{ $value ?? 1 }}" class="peer sr-only" {{ $checked ? 'checked' : '' }}>
    <span aria-hidden="true" class="relative shrink-0 w-10 h-[22px] rounded-full bg-slate-300 dark:bg-slate-600 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/25
        after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:w-4 after:h-4 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-[18px]"></span>
</label>
