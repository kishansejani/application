{{-- Login | Sign up switch. @include('frontend.auth._tabs', ['active' => 'login'|'register']) --}}
@php $isReg = ($active ?? 'login') === 'register'; @endphp
<nav class="fx-auth-tabs relative grid grid-cols-2 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800/70 ring-1 ring-inset ring-slate-200/70 dark:ring-slate-700/60" aria-label="{{ __('auth_ui.tab_login') }} / {{ __('auth_ui.tab_register') }}" data-auth-tabs="{{ $isReg ? 'register' : 'login' }}">
    <span class="fx-auth-tabs-pill absolute top-1 bottom-1 left-1 w-[calc(50%-.25rem)] rounded-xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 {{ $isReg ? 'translate-x-full' : '' }}" aria-hidden="true"></span>
    <a href="{{ route('login') }}" data-auth-tab="login" @if(!$isReg) aria-current="page" @endif
       class="relative z-10 h-10 inline-flex items-center justify-center gap-2 rounded-xl text-[13.5px] font-bold transition-colors {{ $isReg ? 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' : 'text-slate-900 dark:text-white' }}">
        <i class="ph-bold ph-sign-in"></i>{{ __('auth_ui.tab_login') }}
    </a>
    <a href="{{ route('register') }}" data-auth-tab="register" @if($isReg) aria-current="page" @endif
       class="relative z-10 h-10 inline-flex items-center justify-center gap-2 rounded-xl text-[13.5px] font-bold transition-colors {{ $isReg ? 'text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">
        <i class="ph-bold ph-user-plus"></i>{{ __('auth_ui.tab_register') }}
    </a>
</nav>
@once
    @push('scripts')
    <script>
        // Slide the pill from the tab the user came from (purely cosmetic)
        (function () {
            var nav = document.querySelector('[data-auth-tabs]');
            if (!nav) return;
            var pill = nav.querySelector('.fx-auth-tabs-pill'), now = nav.getAttribute('data-auth-tabs'), from = null;
            try { from = sessionStorage.getItem('fxAuthTabFrom'); sessionStorage.removeItem('fxAuthTabFrom'); } catch (e) {}
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (from && from !== now && !reduce && pill.animate) {
                pill.animate([{ transform: now === 'register' ? 'translateX(0)' : 'translateX(100%)' }, { transform: now === 'register' ? 'translateX(100%)' : 'translateX(0)' }],
                    { duration: 380, easing: 'cubic-bezier(.34,1.56,.64,1)' });
            }
            nav.addEventListener('click', function (e) {
                var a = e.target.closest('[data-auth-tab]');
                if (!a) return;
                try { sessionStorage.setItem('fxAuthTabFrom', now); } catch (err) {}
                // keep the typed phone number when switching
                var phone = document.querySelector('input[name=phone]');
                if (phone && /^\d{10}$/.test(phone.value)) { e.preventDefault(); location.href = a.href + '?phone=' + phone.value; }
            });
        })();
    </script>
    @endpush
@endonce
