{{-- Brand panel for the password-reset screens (frontend.layouts.auth @section('panel')) --}}
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 ring-1 ring-white/15 text-[11px] font-bold uppercase tracking-wider text-brand-200"><i class="ph-fill ph-shield-check"></i>{{ __('auth_ui.forgot_badge') }}</span>
<h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight">{{ __('auth_ui.forgot_panel_title') }}</h1>
<p class="mt-4 text-[15px] text-slate-300 leading-relaxed">{{ __('auth_ui.forgot_panel_sub') }}</p>
<ul class="mt-8 space-y-3 text-[13px]">
    @foreach([['ph-user-circle', __('auth_ui.forgot_step1')], ['ph-chat-circle-dots', __('auth_ui.forgot_step2', ['length' => config('otp.length', 4)])], ['ph-lock-key', __('auth_ui.forgot_step3')]] as $n => [$ic, $txt])
        <li class="flex items-center gap-3 {{ ($step ?? 1) > $n + 1 ? 'opacity-60' : '' }}">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ ($step ?? 1) === $n + 1 ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/30' : 'bg-brand-500/20 text-brand-300' }}"><i class="ph-fill {{ ($step ?? 1) > $n + 1 ? 'ph-check-circle' : $ic }}"></i></span>
            <span class="text-slate-200 font-semibold">{{ $txt }}</span>
        </li>
    @endforeach
</ul>
