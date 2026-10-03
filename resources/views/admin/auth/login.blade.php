@extends('frontend.layouts.auth')

@section('title', 'Admin sign in')

@section('panel')
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 ring-1 ring-white/15 text-[11px] font-bold uppercase tracking-wider text-brand-200"><i class="ph-fill ph-shield-check"></i>Secure staff access</span>
    <h1 class="mt-5 text-4xl xl:text-[2.75rem] font-extrabold leading-[1.1] tracking-tight">Run every order, shelf and slot from one place.</h1>
    <p class="mt-4 text-[15px] text-slate-300 leading-relaxed">Manage products and stock, dispatch 2-hour express deliveries, publish offers and print invoices — in English and ગુજરાતી.</p>
    <ul class="mt-8 grid grid-cols-2 gap-3 text-[13px]">
        @foreach([['ph-package', 'Orders & dispatch'], ['ph-stack', 'Stock control'], ['ph-seal-percent', 'Offers & coupons'], ['ph-chart-line-up', 'Sales insights']] as [$ic, $label])
            <li class="flex items-center gap-2.5 p-3 rounded-xl bg-white/5 ring-1 ring-white/10">
                <span class="w-8 h-8 rounded-lg bg-brand-500/20 text-brand-300 flex items-center justify-center"><i class="ph-duotone {{ $ic }} text-lg"></i></span>
                <span class="font-semibold text-slate-200">{{ $label }}</span>
            </li>
        @endforeach
    </ul>
@endsection

@section('content')
    <div>
        <span class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-2xl"><i class="ph-duotone ph-lock-key"></i></span>
        <h2 class="mt-5 text-2xl sm:text-[1.75rem] font-extrabold text-slate-900 dark:text-white tracking-tight">Sign in to the admin panel</h2>
        <p class="mt-1.5 text-[14px] text-slate-500 dark:text-slate-400">Use your staff email or registered mobile number.</p>
    </div>

    @if(session('error'))
        <div role="alert" class="mt-6 flex items-start gap-3 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-200 text-[13px] font-semibold">
            <i class="ph-fill ph-warning-circle text-rose-500 text-lg shrink-0"></i><span>{{ session('error') }}</span>
        </div>
    @endif
    @if(session('success'))
        <div role="status" class="mt-6 flex items-start gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-200 text-[13px] font-semibold">
            <i class="ph-fill ph-check-circle text-emerald-500 text-lg shrink-0"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.login.post') }}" method="POST" class="mt-6 space-y-4" id="adminLoginForm">
        @csrf
        <div>
            <label for="login" class="fx-label">Email or phone</label>
            <div class="relative">
                <i class="ph ph-user-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="admin@grocery.com or 9876543210"
                       class="fx-input !h-12 !pl-11 @error('login') is-invalid @enderror">
            </div>
            @error('login')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="fx-label !mb-0">Password</label>
                <a href="{{ route('password.forgot') }}" class="text-[12px] font-bold text-brand-700 dark:text-brand-400 hover:underline">Forgot password?</a>
            </div>
            <div class="relative">
                <i class="ph ph-lock-simple absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                       class="fx-input !h-12 !pl-11 !pr-12 @error('password') is-invalid @enderror">
                <button type="button" data-toggle-password="password" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center" aria-label="Show password" aria-pressed="false">
                    <i class="ph ph-eye text-lg"></i>
                </button>
            </div>
            @error('password')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
            <p id="capsHint" class="hidden text-[11px] font-semibold text-amber-600 mt-1 items-center gap-1"><i class="ph-fill ph-warning"></i>Caps Lock is on</p>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-emerald-600" {{ old('remember') ? 'checked' : '' }}>
            <span class="text-[13px] font-semibold text-slate-600 dark:text-slate-300">Keep me signed in</span>
        </label>

        <button type="submit" class="fx-btn fx-btn-primary fx-btn-lg w-full !h-12">
            <i class="ph-bold ph-sign-in"></i><span>Sign in to dashboard</span>
        </button>
    </form>

    <!-- Demo credentials -->
    <div class="mt-6 rounded-2xl border border-dashed border-amber-300 dark:border-amber-500/40 bg-amber-50/70 dark:bg-amber-500/5 p-4">
        <div class="flex items-center justify-between gap-3">
            <p class="text-[12px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-300 flex items-center gap-1.5"><i class="ph-fill ph-key"></i>Demo credentials</p>
            <button type="button" id="useDemoBtn" class="fx-btn fx-btn-sm bg-amber-500 hover:bg-amber-600 text-white">Use demo</button>
        </div>
        <dl class="mt-3 grid grid-cols-[auto,1fr] gap-x-3 gap-y-1.5 text-[13px]">
            <dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="font-mono font-bold text-slate-800 dark:text-slate-100 break-all">superadmin@grocery.com</dd>
            <dt class="text-slate-500 dark:text-slate-400">Password</dt><dd class="font-mono font-bold text-slate-800 dark:text-slate-100">admin123</dd>
        </dl>
    </div>

    <p class="mt-8 text-center text-[12px] text-slate-400">Not staff? <a href="{{ route('home') }}" class="font-bold text-slate-600 dark:text-slate-300 hover:text-brand-700">Return to the storefront</a></p>
@endsection

@push('scripts')
<script>
    document.getElementById('useDemoBtn').addEventListener('click', function () {
        document.getElementById('login').value = 'superadmin@grocery.com';
        var p = document.getElementById('password');
        p.value = 'admin123';
        p.focus();
    });
    (function () {
        var p = document.getElementById('password'), hint = document.getElementById('capsHint');
        function check(e) { var on = e.getModifierState && e.getModifierState('CapsLock'); hint.classList.toggle('hidden', !on); hint.classList.toggle('flex', !!on); }
        p.addEventListener('keydown', check); p.addEventListener('keyup', check);
    })();
</script>
@endpush
