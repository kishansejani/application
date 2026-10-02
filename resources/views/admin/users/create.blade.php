@extends('admin.layouts.admin')

@section('title', 'Add New User')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create User Account</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Add a new admin, staff member, or customer.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-sm">
            Back to Users
        </a>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Ramesh Patel" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                    @error('name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Mobile Number *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="10-digit phone" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                    @error('phone') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="user@example.com"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                    @error('email') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Assign Role *</label>
                    <select name="role_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->display_name }} ({{ $r->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Password *</label>
                    <input type="password" name="password" placeholder="Minimum 6 characters" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                    @error('password') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded text-black focus:ring-black">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Active Account (can login immediately)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 btn-theme-primary font-bold rounded-xl text-xs shadow-md transition active:scale-95 flex items-center gap-2">
                <i class="fas fa-user-plus"></i>
                <span>Create User</span>
            </button>
        </div>
    </form>
</div>
@endsection
