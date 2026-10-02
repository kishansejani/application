@extends('admin.layouts.admin')

@section('title', 'Users Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Users & Accounts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage super admins, store staff, and customer accounts.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Add New User</span>
        </a>
    </div>

    <!-- Top KPI / Pipeline Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Card 1: Total Accounts -->
        <a href="{{ route('admin.users.index') }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ !request('role') ? 'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-md border border-slate-700/50' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ !request('role') ? 'text-slate-300' : 'text-slate-500 dark:text-slate-400' }}">Total Accounts</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ !request('role') ? 'bg-white/10 text-indigo-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ !request('role') ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['total'] ?? $users->total() }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ !request('role') ? 'bg-white/10 text-slate-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">Total</span>
            </div>
            <i class="fa-solid fa-users absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 2: Super Admins -->
        <a href="{{ route('admin.users.index', ['role' => 'super_admin']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('role') == 'super_admin' ? 'bg-gradient-to-br from-purple-600 to-indigo-700 text-white shadow-md border border-purple-500' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-purple-500 shadow-sm hover:border-purple-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ request('role') == 'super_admin' ? 'text-purple-100' : 'text-purple-700 dark:text-purple-400' }}">Super Admins</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('role') == 'super_admin' ? 'bg-white/20 text-white' : 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400' }}">
                    <i class="fa-solid fa-crown"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ request('role') == 'super_admin' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['super_admin'] ?? 0 }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ request('role') == 'super_admin' ? 'bg-white/20 text-white' : 'bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300' }}">Owners</span>
            </div>
            <i class="fa-solid fa-crown absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 3: Store Staff -->
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('role') == 'admin' ? 'bg-gradient-to-br from-blue-600 to-cyan-700 text-white shadow-md border border-blue-500' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-blue-500 shadow-sm hover:border-blue-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ request('role') == 'admin' ? 'text-blue-100' : 'text-blue-700 dark:text-blue-400' }}">Store Staff</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('role') == 'admin' ? 'bg-white/20 text-white' : 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' }}">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ request('role') == 'admin' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['admin'] ?? 0 }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ request('role') == 'admin' ? 'bg-white/20 text-white' : 'bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300' }}">Managers</span>
            </div>
            <i class="fa-solid fa-user-shield absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 4: Customers -->
        <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="group relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('role') == 'user' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md border border-emerald-500' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-t-4 border-t-emerald-500 shadow-sm hover:border-emerald-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider {{ request('role') == 'user' ? 'text-emerald-100' : 'text-emerald-700 dark:text-emerald-400' }}">Customers</span>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ request('role') == 'user' ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' }}">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black {{ request('role') == 'user' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['customer'] ?? 0 }}</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full flex items-center gap-1 {{ request('role') == 'user' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Buyers
                </span>
            </div>
            <i class="fa-solid fa-basket-shopping absolute -right-3 -bottom-3 text-5xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email..."
                       class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
            </div>
            <div>
                <select name="role" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-black text-white dark:bg-slate-700 dark:hover:bg-slate-600 font-semibold rounded-xl text-sm transition">
                    Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-sm flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Registered</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">ID: #{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs text-slate-800 dark:text-slate-200 font-semibold">{{ $user->phone }}</div>
                            @if($user->email)
                            <div class="text-xs text-slate-400">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : ($user->role === 'admin' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300') }}">
                                {{ $user->roleModel ? $user->roleModel->display_name : ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">
                            {{ $user->created_at->format('d M, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="p-2 text-slate-500 hover:text-amber-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                        <i class="fas fa-power-off text-sm"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.users.edit', $user) }}" title="Edit User"
                                   class="p-2 text-slate-500 hover:text-black dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete User"
                                            class="p-2 text-slate-500 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">No users found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
