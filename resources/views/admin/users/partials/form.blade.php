{{-- Shared user form. Expects $roles and optional $user. --}}
@php
    $user = $user ?? null;
    $isActive = old('_token') ? (bool) old('is_active') : ($user ? (bool) $user->is_active : true);
    $selectedRole = old('role_id', $user->role_id ?? null);
    $err = fn ($f) => $errors->has($f) ? ' !border-rose-400' : '';
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
    <div class="xl:col-span-2 space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-identification-card"></i> Profile</h3>
                    <p class="card-subtitle">The mobile number is used to sign in and must be unique.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="userName" class="form-label">Full name <span class="text-rose-500">*</span></label>
                    <input type="text" id="userName" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="e.g. Ramesh Patel" required maxlength="100" autocomplete="name" class="form-control{{ $err('name') }}">
                    @error('name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="userPhone" class="form-label">Mobile number <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[13px] font-bold text-slate-400 pointer-events-none">+91</span>
                        <input type="tel" id="userPhone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="10-digit number" required maxlength="15" inputmode="tel" autocomplete="tel" class="form-control !pl-11{{ $err('phone') }}">
                    </div>
                    @error('phone')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="userEmail" class="form-label">Email address</label>
                    <input type="email" id="userEmail" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="user@example.com" maxlength="150" autocomplete="email" class="form-control{{ $err('email') }}">
                    @error('email')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-password"></i> {{ $user ? 'Change password' : 'Password' }}</h3>
                    <p class="card-subtitle">{{ $user ? 'Leave blank to keep the current password.' : 'At least 6 characters. Share it with the user securely.' }}</p>
                </div>
            </div>
            <div class="card-body">
                <label for="userPassword" class="form-label">{{ $user ? 'New password' : 'Password' }} @unless($user)<span class="text-rose-500">*</span>@endunless</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="password" id="userPassword" name="password" placeholder="{{ $user ? 'Leave blank to keep unchanged' : 'Minimum 6 characters' }}" @unless($user) required @endunless minlength="6" autocomplete="new-password" class="form-control !pr-11 font-mono{{ $err('password') }}">
                        <button type="button" id="togglePassword" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white" title="Show password" aria-label="Show password" aria-pressed="false"><i class="ph ph-eye text-lg"></i></button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="generatePassword" class="btn btn-outline flex-1 sm:flex-none"><i class="ph ph-shuffle"></i> Generate</button>
                        <button type="button" id="copyPassword" class="btn btn-outline flex-1 sm:flex-none" title="Copy password"><i class="ph ph-copy"></i> Copy</button>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center gap-2">
                    <div class="flex-1 grid grid-cols-4 gap-1" aria-hidden="true">
                        @for($i = 0; $i < 4; $i++)<span class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-700" data-strength-bar></span>@endfor
                    </div>
                    <span id="strengthLabel" class="text-[11.5px] font-bold text-slate-400 w-20 text-right"></span>
                </div>
                @error('password')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-shield-check"></i> Role &amp; access</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="userRole" class="form-label">Role <span class="text-rose-500">*</span></label>
                    <select id="userRole" name="role_id" required class="form-select w-full{{ $err('role_id') }}" data-search>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ (string) $selectedRole === (string) $r->id ? 'selected' : '' }}>{{ $r->display_name }} ({{ $r->name }})</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Permissions come from the role. <a href="{{ route('admin.roles.index') }}" class="font-bold underline">Manage roles</a></p>
                    @error('role_id')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>
                        <span class="block text-[13px] font-bold text-slate-800 dark:text-slate-100">Active account</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $user ? 'Inactive users cannot sign in.' : 'The user can sign in immediately.' }}</span>
                    </span>
                </label>
                @if($user)
                    <dl class="text-[12px] space-y-1 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex justify-between"><dt class="text-slate-500">User ID</dt><dd class="font-mono font-bold text-slate-700 dark:text-slate-200">{{ $user->id }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Registered</dt><dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->created_at?->format('d M Y') }}</dd></div>
                    </dl>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body flex gap-2.5">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline flex-1 justify-center">Cancel</a>
                <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph {{ $user ? 'ph-check' : 'ph-user-plus' }}"></i> {{ $user ? 'Update user' : 'Create user' }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('userPassword');
        const toggle = document.getElementById('togglePassword');
        const bars = document.querySelectorAll('[data-strength-bar]');
        const label = document.getElementById('strengthLabel');

        function setVisible(show) {
            input.type = show ? 'text' : 'password';
            toggle.innerHTML = '<i class="ph ' + (show ? 'ph-eye-slash' : 'ph-eye') + ' text-lg"></i>';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.title = show ? 'Hide password' : 'Show password';
            toggle.setAttribute('aria-label', toggle.title);
        }
        toggle.addEventListener('click', () => setVisible(input.type === 'password'));

        function strength(v) {
            if (!v) return 0;
            let s = 0;
            if (v.length >= 6) s++;
            if (v.length >= 10) s++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
            if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
            return Math.max(1, s);
        }
        const levels = [['', ''], ['Weak', 'bg-rose-500'], ['Fair', 'bg-amber-500'], ['Good', 'bg-blue-500'], ['Strong', 'bg-emerald-500']];
        function paint() {
            const s = strength(input.value);
            bars.forEach((b, i) => {
                b.className = 'h-1.5 rounded-full ' + (i < s ? levels[s][1] : 'bg-slate-200 dark:bg-slate-700');
            });
            label.textContent = levels[s][0];
        }
        input.addEventListener('input', paint);

        document.getElementById('generatePassword').addEventListener('click', function () {
            const sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '@#$%&*!?'];
            const all = sets.join('');
            const rnd = (n) => { const a = new Uint32Array(1); (window.crypto || window.msCrypto).getRandomValues(a); return a[0] % n; };
            let chars = sets.map(s => s[rnd(s.length)]);
            while (chars.length < 12) chars.push(all[rnd(all.length)]);
            for (let i = chars.length - 1; i > 0; i--) { const j = rnd(i + 1); [chars[i], chars[j]] = [chars[j], chars[i]]; }
            input.value = chars.join('');
            setVisible(true);
            paint();
            if (window.toastr) toastr.info('Strong password generated. Copy it before saving.');
        });

        document.getElementById('copyPassword').addEventListener('click', function () {
            if (!input.value) { if (window.toastr) toastr.warning('Enter or generate a password first.'); return; }
            copyToClipboard(input.value, 'Password copied');
        });
        paint();
    });
</script>
@endpush
