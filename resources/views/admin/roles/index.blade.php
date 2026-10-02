@extends('admin.layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Roles & Permissions Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure access control levels, user roles, and module permission matrix.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-black hover:bg-slate-900 text-white dark:bg-white dark:text-black dark:hover:bg-slate-200 font-semibold rounded-xl text-sm shadow-md transition active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Add New Role</span>
        </a>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-2 mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $role->name === 'super_admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : ($role->name === 'admin' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300') }}">
                        {{ $role->name }}
                    </span>
                    <span class="text-xs font-semibold text-slate-400">
                        <i class="fas fa-users mr-1"></i> {{ $role->users->count() }} users
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $role->display_name }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                    {{ $role->description ?: 'System role for managing specific platform features.' }}
                </p>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-2">Granted Permissions ({{ $role->name === 'super_admin' ? 'All (Full Access)' : $role->permissions->count() }}):</span>
                    <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto">
                        @if($role->name === 'super_admin')
                            <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-300 text-[11px] font-semibold rounded-lg">
                                <i class="fas fa-crown text-[10px] mr-1"></i> All Permissions (Super Admin Bypass)
                            </span>
                        @else
                            @forelse($role->permissions as $perm)
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-medium rounded-md">
                                    {{ $perm->display_name }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No specific admin permissions granted.</span>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <a href="{{ route('admin.roles.edit', $role) }}" class="text-xs font-bold text-slate-700 hover:text-black dark:text-slate-300 dark:hover:text-white inline-flex items-center gap-1">
                    <i class="fas fa-edit"></i> Edit Permissions
                </a>
                @if(!in_array($role->name, ['super_admin', 'admin', 'user']))
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
