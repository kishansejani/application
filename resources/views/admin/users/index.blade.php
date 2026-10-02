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
        <a href="{{ route('admin.users.index') }}" class="group relative overflow-hidden rounded-2xl p-4.5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ !request('role') ? 'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-lg border border-slate-700/60' : 'bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700 hover:border-slate-300 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block {{ !request('role') ? 'text-slate-300' : 'text-slate-500 dark:text-slate-400' }}">Total Accounts</span>
                    <div class="text-3xl font-black mt-2 {{ !request('role') ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['total'] ?? $users->total() }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-sm shadow-inner {{ !request('role') ? 'bg-white/10 text-indigo-300 border border-white/20 backdrop-blur-md' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ !request('role') ? 'bg-white/10 text-slate-200' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">● Registered Users</span>
            </div>
            <i class="fa-solid fa-users absolute -right-3 -bottom-3 text-6xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 2: Super Admins -->
        <a href="{{ route('admin.users.index', ['role' => 'super_admin']) }}" class="group relative overflow-hidden rounded-2xl p-4.5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('role') == 'super_admin' ? 'bg-gradient-to-br from-purple-600 to-indigo-700 text-white shadow-lg border border-purple-500' : 'bg-gradient-to-br from-white via-white to-purple-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-purple-950/30 border border-purple-200/80 dark:border-purple-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block {{ request('role') == 'super_admin' ? 'text-purple-100' : 'text-purple-700 dark:text-purple-400' }}">Super Admins</span>
                    <div class="text-3xl font-black mt-2 {{ request('role') == 'super_admin' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['super_admin'] ?? 0 }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-sm text-white shadow-md {{ request('role') == 'super_admin' ? 'bg-white/20 text-white border border-white/30' : 'bg-gradient-to-tr from-purple-500 to-indigo-500 shadow-purple-500/25' }}">
                    <i class="fa-solid fa-crown"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ request('role') == 'super_admin' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800' }}">
                    ★ Full Authority
                </span>
            </div>
            <i class="fa-solid fa-crown absolute -right-3 -bottom-3 text-6xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 3: Store Staff -->
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="group relative overflow-hidden rounded-2xl p-4.5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('role') == 'admin' ? 'bg-gradient-to-br from-blue-600 to-cyan-700 text-white shadow-lg border border-blue-500' : 'bg-gradient-to-br from-white via-white to-blue-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-blue-950/30 border border-blue-200/80 dark:border-blue-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block {{ request('role') == 'admin' ? 'text-blue-100' : 'text-blue-700 dark:text-blue-400' }}">Store Staff</span>
                    <div class="text-3xl font-black mt-2 {{ request('role') == 'admin' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['admin'] ?? 0 }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-sm text-white shadow-md {{ request('role') == 'admin' ? 'bg-white/20 text-white border border-white/30' : 'bg-gradient-to-tr from-blue-500 to-cyan-400 shadow-blue-500/25' }}">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ request('role') == 'admin' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800' }}">
                    ● Operations
                </span>
            </div>
            <i class="fa-solid fa-user-shield absolute -right-3 -bottom-3 text-6xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>

        <!-- Card 4: Customers -->
        <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="group relative overflow-hidden rounded-2xl p-4.5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ request('role') == 'user' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg border border-emerald-500' : 'bg-gradient-to-br from-white via-white to-emerald-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 shadow-sm' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider block {{ request('role') == 'user' ? 'text-emerald-100' : 'text-emerald-700 dark:text-emerald-400' }}">Customers</span>
                    <div class="text-3xl font-black mt-2 {{ request('role') == 'user' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $stats['customer'] ?? 0 }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-sm text-white shadow-md {{ request('role') == 'user' ? 'bg-white/20 text-white border border-white/30' : 'bg-gradient-to-tr from-emerald-500 to-teal-400 shadow-emerald-500/25' }}">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ request('role') == 'user' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Active Buyers
                </span>
            </div>
            <i class="fa-solid fa-basket-shopping absolute -right-3 -bottom-3 text-6xl opacity-5 dark:opacity-10 pointer-events-none group-hover:scale-110 transition-transform"></i>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/90 dark:border-slate-700/80 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email..."
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-slate-400">
            </div>
            <div>
                <select name="role" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 font-bold rounded-xl text-xs shadow-sm transition active:scale-95">
                    Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs flex items-center justify-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/80 overflow-hidden shadow-sm p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3.5 rounded-l-2xl">User</th>
                        <th class="px-4 py-3.5">Contact</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Registered</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-slate-900 dark:text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-slate-800 to-slate-600 text-white font-black text-sm flex items-center justify-center shadow-sm">
                                    {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $user->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">ID: #{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-mono text-xs text-slate-800 dark:text-slate-200 font-bold">{{ $user->phone }}</div>
                            @if($user->email)
                            <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-3 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider border {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-800' : ($user->role === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600') }}">
                                {{ $user->roleModel ? $user->roleModel->display_name : ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                            {{ $user->created_at->format('d M, Y') }}
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 hover:bg-amber-600 hover:text-white flex items-center justify-center transition shadow-sm">
                                        <i class="fas fa-power-off text-xs"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.users.edit', $user) }}" title="Edit User"
                                   class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete User"
                                            class="confirm-delete-btn w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center transition shadow-sm">
                                        <i class="fas fa-trash-alt text-xs"></i>
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
        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
