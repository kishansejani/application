{{--
    Animated one-time-code entry, shared by the customer login / sign-up verify page
    and the password-reset verify page.

    @include('frontend.auth._otp-verify', [
        'action'        => route(...),        // POST target (JSON-aware)
        'resendAction'  => route(...),        // POST target for "resend"
        'channel'       => 'sms' | 'email',   // picks the illustration
        'target'        => '+91 98765 43210', // shown in the subtitle
        'changeUrl'     => url, 'changeLabel' => text,
        'length'        => 4, 'resendIn' => 23, 'resendSeconds' => 30, 'expiryMinutes' => 5,
        'demoCode'      => '1234' | null,
        'title'         => optional heading,
        'notice'        => optional success line, 'error' => optional error (shakes on load),
    ])
--}}
@php
    $length = (int) ($length ?? 4);
    $channel = $channel ?? 'sms';
    $resendSeconds = max(1, (int) ($resendSeconds ?? 30));
    $resendIn = (int) ($resendIn ?? 0);
    $title = $title ?? ($channel === 'email' ? __('auth_ui.verify_title_email') : __('auth_ui.verify_title'));
@endphp

@once
    <link rel="stylesheet" href="{{ asset('assets/front/otp-verify.css') }}?v=3">
@endonce

<div class="fx-otp" data-otp
     data-length="{{ $length }}"
     data-resend-in="{{ $resendIn }}"
     data-resend-total="{{ $resendSeconds }}"
     data-error="{{ $error ?? '' }}"
     data-i18n="{{ json_encode([
         'verifying' => __('auth_ui.verifying'),
         'verified' => __('auth_ui.verified'),
         'redirecting' => __('auth_ui.redirecting'),
         'network' => __('auth_ui.network_error'),
         'incomplete' => __('auth_ui.otp_invalid_length', ['length' => $length]),
     ]) }}">

    {{-- ===== Illustration (pure SVG + CSS) ===== --}}
    <div class="fx-otp-art fx-otp-art--{{ $channel }}" aria-hidden="true">
        <span class="fx-otp-ring r1"></span><span class="fx-otp-ring r2"></span><span class="fx-otp-ring r3"></span>
        <svg viewBox="0 0 200 160" class="fx-otp-svg">
            <defs>
                <linearGradient id="fxOtpShield" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#34d399"/><stop offset="1" stop-color="#059669"/>
                </linearGradient>
                <linearGradient id="fxOtpShieldOk" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#4ade80"/><stop offset="1" stop-color="#16a34a"/>
                </linearGradient>
                <filter id="fxOtpShadow" x="-30%" y="-30%" width="160%" height="160%">
                    <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#0f172a" flood-opacity=".14"/>
                </filter>
            </defs>

            @if($channel === 'email')
                {{-- envelope with a code card sliding out --}}
                <g class="fx-otp-device">
                    <rect x="46" y="62" width="108" height="74" rx="12" class="fx-otp-surface" filter="url(#fxOtpShadow)"/>
                    <g class="fx-otp-letter">
                        <rect x="58" y="40" width="84" height="62" rx="8" class="fx-otp-paper"/>
                        <rect x="68" y="52" width="40" height="5" rx="2.5" class="fx-otp-line"/>
                        <rect x="68" y="62" width="62" height="4" rx="2" class="fx-otp-line soft"/>
                        <g class="fx-otp-dots">
                            <circle cx="78" cy="82" r="4"/><circle cx="92" cy="82" r="4"/><circle cx="106" cy="82" r="4"/><circle cx="120" cy="82" r="4"/>
                        </g>
                    </g>
                    <path d="M46 74 L100 108 L154 74 L154 124 a12 12 0 0 1 -12 12 L58 136 a12 12 0 0 1 -12 -12 Z" class="fx-otp-surface fx-otp-stroke"/>
                    <path d="M46 132 L88 100 M154 132 L112 100" class="fx-otp-fold"/>
                </g>
                <g class="fx-otp-shield" transform="translate(132 104)">
            @else
                {{-- phone --}}
                <g class="fx-otp-device">
                    <rect x="68" y="18" width="64" height="124" rx="14" class="fx-otp-surface fx-otp-stroke" filter="url(#fxOtpShadow)"/>
                    <rect x="90" y="25" width="20" height="5" rx="2.5" class="fx-otp-line"/>
                    <rect x="78" y="112" width="44" height="5" rx="2.5" class="fx-otp-line soft"/>
                    <rect x="86" y="122" width="28" height="4" rx="2" class="fx-otp-line soft"/>
                </g>
                {{-- message bubble flying in --}}
                <g class="fx-otp-bubble">
                    <path d="M118 22 h56 a10 10 0 0 1 10 10 v18 a10 10 0 0 1 -10 10 h-40 l-10 9 v-9 h-6 a10 10 0 0 1 -10 -10 v-18 a10 10 0 0 1 10 -10 Z" class="fx-otp-paper" filter="url(#fxOtpShadow)"/>
                    <g class="fx-otp-dots">
                        <circle cx="132" cy="41" r="4"/><circle cx="146" cy="41" r="4"/><circle cx="160" cy="41" r="4"/><circle cx="174" cy="41" r="4" class="last"/>
                    </g>
                </g>
                <g class="fx-otp-shield" transform="translate(100 74)">
            @endif
                    {{-- shield (shared) - lock keyhole morphs into a check mark on success --}}
                    <g class="fx-otp-shield-body">
                        <path d="M0 -26 L21 -18 V-2 C21 13 12 22 0 27 C-12 22 -21 13 -21 -2 V-18 Z" class="fx-otp-shield-fill"/>
                        <path d="M0 -26 L21 -18 V-2 C21 13 12 22 0 27 C-12 22 -21 13 -21 -2 V-18 Z" class="fx-otp-shield-gloss"/>
                        <g class="fx-otp-lock">
                            <circle cx="0" cy="-3" r="5" fill="#fff"/>
                            <path d="M-2.4 0 h4.8 l1.2 9 h-7.2 Z" fill="#fff"/>
                        </g>
                        <path class="fx-otp-check" d="M-9 0 L-2.5 7 L10 -7" fill="none" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                </g>

            <g class="fx-otp-sparkles">
                <path d="M34 40 l2.5 6 6 2.5 -6 2.5 -2.5 6 -2.5 -6 -6 -2.5 6 -2.5 Z"/>
                <path d="M168 112 l2 4.5 4.5 2 -4.5 2 -2 4.5 -2 -4.5 -4.5 -2 4.5 -2 Z"/>
                <circle cx="44" cy="118" r="3"/>
                <circle cx="182" cy="80" r="2.5"/>
            </g>
        </svg>
    </div>

    {{-- ===== Heading ===== --}}
    <div class="text-center">
        <h1 class="fx-otp-title text-2xl sm:text-[1.7rem] font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h1>
        <p class="fx-otp-sub mt-1.5 text-[13.5px] text-slate-500 dark:text-slate-400">
            {{ __('auth_ui.code_sent_to', ['length' => $length]) }}
            <span class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                <strong class="font-extrabold text-slate-800 dark:text-slate-100 {{ $channel === 'email' ? 'break-all' : 'font-mono tracking-wide' }}">{{ $target }}</strong>
                <a href="{{ $changeUrl }}" class="inline-flex items-center gap-1 text-[12px] font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline hover:text-emerald-700 dark:hover:text-emerald-300 ml-1">
                    <i class="ph-bold ph-pencil-simple"></i>{{ $changeLabel }}
                </a>
            </span>
        </p>
        @if(!empty($demoCode))
            <button type="button" data-otp-fill="{{ $demoCode }}" class="fx-otp-demo mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-[12px] font-bold text-amber-800 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-500/20 shadow-sm transition-all transform active:scale-95">
                <i class="ph-fill ph-lightning text-amber-500 text-sm"></i>
                <span>{!! str_replace('__CODE__', '<strong class="font-mono tracking-widest text-amber-900 dark:text-amber-100">' . e($demoCode) . '</strong>', e(__('auth_ui.demo_hint', ['code' => '__CODE__']))) !!}</span>
            </button>
        @endif
    </div>

    {{-- ===== Code form ===== --}}
    <form action="{{ $action }}" method="POST" class="fx-otp-form mt-7" novalidate>
        @csrf
        <input type="hidden" name="otp" value="" data-otp-value>
        <noscript>
            <style>.fx-otp-boxes{display:none!important}</style>
            <input type="text" name="otp" inputmode="numeric" maxlength="{{ $length }}" required class="w-full h-14 text-center text-2xl font-mono tracking-[.5em] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" aria-label="{{ __('auth_ui.otp_label') }}">
        </noscript>

        <div class="fx-otp-boxes" role="group" aria-label="{{ __('auth_ui.otp_label') }}" style="--n: {{ $length }}">
            @for($i = 0; $i < $length; $i++)
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $length }}"
                       class="fx-otp-box" style="--i: {{ $i }}" data-otp-box
                       autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" @if($i === 0) autofocus @endif
                       aria-label="{{ __('auth_ui.otp_digit', ['n' => $i + 1, 'total' => $length]) }}">
            @endfor
        </div>

        <p class="fx-otp-msg font-semibold text-xs" data-otp-msg role="alert" aria-live="assertive">{{ $error ?? '' }}</p>
        @if(!empty($notice) && empty($error))
            <p class="fx-otp-notice" data-otp-notice role="status"><i class="ph-fill ph-paper-plane-tilt"></i><span>{{ $notice }}</span></p>
        @else
            <p class="fx-otp-notice hidden" data-otp-notice role="status"><i class="ph-fill ph-paper-plane-tilt"></i><span></span></p>
        @endif

        <button type="submit" class="w-full h-12 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:from-emerald-700 active:to-teal-700 text-white font-extrabold text-[14.5px] shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 transition-all duration-200 flex items-center justify-center gap-2 relative overflow-hidden group/btn mt-4 fx-otp-submit" data-otp-submit>
            <span class="absolute inset-0 w-1/2 h-full bg-white/20 skew-x-12 -translate-x-full group-hover/btn:translate-x-[300%] transition-transform duration-700 pointer-events-none"></span>
            <span class="fx-otp-submit-label inline-flex items-center gap-2">
                <i class="ph-bold ph-shield-check text-lg"></i>
                <span data-otp-submit-text>{{ __('auth_ui.verify_btn') }}</span>
            </span>
        </button>
    </form>

    {{-- ===== Resend with countdown ring ===== --}}
    <div class="mt-5 flex items-center justify-center gap-2 text-[13px]">
        <span class="text-slate-500 dark:text-slate-400">{{ __('auth_ui.didnt_receive') }}</span>
        <form action="{{ $resendAction }}" method="POST" data-otp-resend-form>
            @csrf
            <button type="submit" class="fx-otp-resend" data-otp-resend @if($resendIn > 0) disabled @endif>
                <span class="fx-otp-countdown" data-otp-countdown @if($resendIn <= 0) hidden @endif>
                    <svg viewBox="0 0 24 24" class="fx-otp-countdown-ring" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" class="track"/>
                        <circle cx="12" cy="12" r="9" class="bar" data-otp-ring style="stroke-dashoffset: {{ round(56.55 * (1 - $resendIn / $resendSeconds), 2) }}"/>
                    </svg>
                    <span>{{ __('auth_ui.resend_in') }}</span>
                    <span class="font-mono tabular-nums" data-otp-timer>0:{{ str_pad((string) $resendIn, 2, '0', STR_PAD_LEFT) }}</span>
                </span>
                <span class="fx-otp-resend-ready" data-otp-ready @if($resendIn > 0) hidden @endif>
                    <i class="ph-bold ph-arrow-clockwise"></i><span>{{ __('auth_ui.resend_code') }}</span>
                </span>
            </button>
        </form>
    </div>

    <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col items-center gap-1.5 text-[12px] text-slate-400 text-center">
        <span class="inline-flex items-center gap-1.5"><i class="ph-bold ph-timer text-emerald-500"></i>{{ __('auth_ui.code_expires', ['minutes' => $expiryMinutes ?? 5]) }}</span>
        <span class="inline-flex items-center gap-1.5"><i class="ph-fill ph-lock-key text-emerald-500"></i>{{ __('auth_ui.secure_note') }}</span>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/front/otp-verify.js') }}?v=3"></script>
    @endpush
@endonce
