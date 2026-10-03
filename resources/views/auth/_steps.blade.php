{{-- Password reset progress. @include('auth._steps', ['step' => 1..3]) --}}
<ol class="flex items-center gap-2 mb-7" aria-label="{{ __('auth_ui.forgot_kicker') }}">
    @foreach([__('auth_ui.step_account'), __('auth_ui.step_verify'), __('auth_ui.step_password')] as $i => $label)
        @php $n = $i + 1; @endphp
        <li class="flex items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}" @if($n === $step) aria-current="step" @endif>
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-[12px] font-extrabold shrink-0 transition-all
                {{ $n < $step ? 'bg-brand-600 text-white' : ($n === $step ? 'bg-brand-600 text-white ring-4 ring-brand-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400') }}">
                @if($n < $step)<i class="ph-bold ph-check"></i>@else{{ $n }}@endif
            </span>
            <span class="text-[12px] font-bold whitespace-nowrap {{ $n <= $step ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400' }} {{ $n === $step ? '' : 'hidden sm:inline' }}">{{ $label }}</span>
            @if(!$loop->last)<span class="flex-1 h-0.5 rounded-full {{ $n < $step ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>@endif
        </li>
    @endforeach
</ol>
