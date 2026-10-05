{{-- Login | Sign up switch. @include('frontend.auth._tabs', ['active' => 'login'|'register']) --}}
@php $isReg = ($active ?? 'login') === 'register'; @endphp
<div class="relative p-1.5 rounded-2xl bg-slate-100/90 dark:bg-slate-800/80 p-1 border border-slate-200/80 dark:border-slate-700/70 shadow-inner backdrop-blur-sm" aria-label="{{ __('auth_ui.tab_login') }} / {{ __('auth_ui.tab_register') }}" data-auth-tabs="{{ $isReg ? 'register' : 'login' }}">
    <div class="relative grid grid-cols-2 gap-1">
        {{-- Sliding Background Indicator --}}
        <span class="fx-auth-tabs-pill absolute top-0 bottom-0 left-0 w-1/2 rounded-xl bg-white dark:bg-slate-900 shadow-md ring-1 ring-slate-900/5 dark:ring-white/10 transition-transform duration-300 ease-out {{ $isReg ? 'translate-x-full' : 'translate-x-0' }}" aria-hidden="true"></span>
        
        <a href="{{ route('login') }}" data-auth-tab="login" @if(!$isReg) aria-current="page" @endif
           class="relative z-10 h-11 inline-flex items-center justify-center gap-2 rounded-xl text-[14px] font-bold transition-all duration-200 {{ $isReg ? 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' : 'text-emerald-700 dark:text-emerald-400 font-extrabold' }}">
            <i class="ph-bold ph-sign-in text-lg {{ !$isReg ? 'text-emerald-600 dark:text-emerald-400 animate-bounce' : '' }}" style="animation-iteration-count: 1;"></i>
            <span>{{ __('auth_ui.tab_login') }}</span>
        </a>
        
        <a href="{{ route('register') }}" data-auth-tab="register" @if($isReg) aria-current="page" @endif
           class="relative z-10 h-11 inline-flex items-center justify-center gap-2 rounded-xl text-[14px] font-bold transition-all duration-200 {{ $isReg ? 'text-emerald-700 dark:text-emerald-400 font-extrabold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="ph-bold ph-user-plus text-lg {{ $isReg ? 'text-emerald-600 dark:text-emerald-400 animate-bounce' : '' }}" style="animation-iteration-count: 1;"></i>
            <span>{{ __('auth_ui.tab_register') }}</span>
        </a>
    </div>
</div>
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
            if (from && from !== now && !reduce && pill && pill.animate) {
                pill.animate([{ transform: now === 'register' ? 'translateX(0)' : 'translateX(100%)' }, { transform: now === 'register' ? 'translateX(100%)' : 'translateX(0)' }],
                    { duration: 320, easing: 'cubic-bezier(.34,1.56,.64,1)' });
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
