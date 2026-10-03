@extends('admin.layouts.admin')

@section('title', 'All roles')

@section('content')
@php
    $systemRoles = ['super_admin', 'admin', 'user'];
    $roleTone = fn ($name) => match ($name) { 'super_admin' => 'badge-violet', 'admin' => 'badge-info', 'user' => 'badge-success', default => 'badge-neutral' };
    $totalPerms = $stats['permissions_count'] ?? 0;
@endphp
    <x-admin.page-header title="Roles & permissions" subtitle="Control what each staff role can see and manage in the admin panel." icon="shield-check">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline"><i class="ph ph-users"></i> Users</a>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add role</a>
    </x-admin.page-header>

    <div class="stat-grid cols-4">
        <x-admin.stat-card label="All roles" :value="$stats['total'] ?? $roles->count()" icon="shield-check" tone="slate" meta="Access levels" />
        <x-admin.stat-card label="System roles" :value="$stats['system_roles'] ?? 0" icon="crown" tone="violet" meta="Protected, cannot be deleted" />
        <x-admin.stat-card label="Permissions" :value="$totalPerms" icon="key" tone="blue" meta="Module rules available" />
        <x-admin.stat-card label="Assigned users" :value="$stats['assigned_users'] ?? 0" icon="user-gear" tone="emerald" :href="route('admin.users.index')" meta="Accounts with a role" />
    </div>

    <div class="card table-card">
        <table id="rolesTable" class="w-full" data-export-title="Roles and permissions">
            <thead>
                <tr>
                    <th>Role</th>
                    <th class="export-only">Key</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th>Type</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    @php
                        $isSuper = $role->name === 'super_admin';
                        $isSystem = in_array($role->name, $systemRoles);
                        $permCount = $isSuper ? $totalPerms : $role->permissions->count();
                        $permNames = $isSuper ? 'All permissions' : ($role->permissions->pluck('display_name')->implode(', ') ?: 'None');
                        $userCount = $role->users->count();
                    @endphp
                    <tr>
                        <td data-export="{{ $role->display_name }}">
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 {{ $isSuper ? 'tone-violet' : ($role->name === 'admin' ? 'tone-blue' : ($role->name === 'user' ? 'tone-emerald' : 'tone-slate')) }}">
                                    <i class="ph-duotone {{ $isSuper ? 'ph-crown' : ($role->name === 'user' ? 'ph-user' : 'ph-user-gear') }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $role->display_name }}</a>
                                    <span class="block font-mono text-[10.5px] text-slate-400 mt-0.5">{{ $role->name }}</span>
                                    <span class="block text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5 max-w-xs truncate">{{ $role->description ?: 'No description' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $role->name }}</td>
                        <td data-order="{{ $permCount }}" data-export="{{ $permNames }}">
                            @if($isSuper)
                                <span class="badge badge-violet"><i class="ph-fill ph-crown"></i> Full access</span>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="text-[12.5px] font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $permCount }} / {{ $totalPerms }}</span>
                                    <span class="block w-20 h-1.5 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden"><span class="block h-full rounded-full bg-emerald-500" style="width: {{ $totalPerms ? round($permCount / $totalPerms * 100) : 0 }}%"></span></span>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-1.5 max-w-md">
                                    @forelse($role->permissions->take(4) as $perm)
                                        <span class="badge badge-neutral !text-[10.5px]">{{ $perm->display_name }}</span>
                                    @empty
                                        <span class="text-[11.5px] text-slate-400">No admin permissions</span>
                                    @endforelse
                                    @if($role->permissions->count() > 4)
                                        <span class="badge badge-neutral !text-[10.5px]" title="{{ $permNames }}">+{{ $role->permissions->count() - 4 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td data-order="{{ $userCount }}" data-export="{{ $userCount }}">
                            <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-bold text-slate-700 dark:text-slate-200 hover:underline whitespace-nowrap"><i class="ph ph-users"></i> {{ $userCount }} {{ \Illuminate\Support\Str::plural('user', $userCount) }}</a>
                        </td>
                        <td data-export="{{ $isSystem ? 'System' : 'Custom' }}">
                            @if($isSystem)
                                <span class="badge {{ $roleTone($role->name) }}"><i class="ph ph-lock-simple"></i> System</span>
                            @else
                                <span class="badge badge-neutral">Custom</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="act-btn is-edit" title="Edit permissions"><i class="ph ph-pencil-simple-line"></i></a>
                                @if(!$isSystem)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete role “{{ $role->display_name }}”?" @if($userCount) data-confirm-text="This role has {{ $userCount }} assigned {{ \Illuminate\Support\Str::plural('user', $userCount) }}. Reassign them first." @endif title="Delete"><i class="ph ph-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#rolesTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 1, visible: false },   // Key: export-only
                { targets: 0, responsivePriority: 1 },
                { targets: 5, responsivePriority: 2 },
                { targets: 2, responsivePriority: 3 }
            ]
        });
    });
</script>
@endpush
