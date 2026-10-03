@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
@php
    $roleBadge = fn ($name) => match ($name) { 'super_admin' => 'badge-violet', 'admin' => 'badge-info', 'user', 'customer', null => 'badge-success', default => 'badge-neutral' };
    $noFilter = !request('role') && !request('search');
@endphp
    <x-admin.page-header title="Users" subtitle="Manage super admins, store staff and customer accounts." icon="users-three">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline"><i class="ph ph-shield-check"></i> Roles</a>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="ph-bold ph-user-plus"></i> Add user</a>
    </x-admin.page-header>

    <div class="stat-grid cols-5">
        <x-admin.stat-card label="All accounts" :value="$stats['total'] ?? $users->count()" icon="users-three" tone="slate"
            :href="route('admin.users.index')" :active="$noFilter" meta="Registered accounts" />
        <x-admin.stat-card label="Super admins" :value="$stats['super_admin'] ?? 0" icon="crown" tone="violet"
            :href="route('admin.users.index', ['role' => 'super_admin'])" :active="request('role') === 'super_admin'" meta="Full access" />
        <x-admin.stat-card label="Store staff" :value="$stats['admin'] ?? 0" icon="user-gear" tone="blue"
            :href="route('admin.users.index', ['role' => 'admin'])" :active="request('role') === 'admin'" meta="Admin panel users" />
        <x-admin.stat-card label="Customers" :value="$stats['customer'] ?? 0" icon="basket" tone="emerald"
            :href="route('admin.users.index', ['role' => 'user'])" :active="request('role') === 'user'" meta="Storefront shoppers" />
        <x-admin.stat-card label="Active" :value="$stats['active'] ?? 0" icon="check-circle" tone="cyan" meta="Can sign in" class="col-span-2 md:col-span-1" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2.5" data-no-loading>
                <div class="relative flex-1 min-w-[12rem] sm:flex-none sm:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Name, phone or email" class="form-control !pl-9" aria-label="Search users">
                </div>
                <select name="role" onchange="this.form.submit()" class="form-select w-auto min-w-[10rem]" aria-label="Role">
                    <option value="">All roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->display_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm"><i class="ph ph-funnel"></i> Apply</button>
                @if(!$noFilter)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm text-rose-600"><i class="ph ph-x-circle"></i> Clear filters</a>
                @endif
            </form>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $users->count() }} {{ \Illuminate\Support\Str::plural('user', $users->count()) }}</span>
        </div>

        <table id="usersTable" class="w-full" data-export-title="Users">
            <thead>
                <tr>
                    <th>User</th>
                    <th class="export-only">Phone</th>
                    <th class="export-only">Email</th>
                    <th class="no-export">Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    @php
                        $roleLabel = $user->roleModel ? $user->roleModel->display_name : ucfirst(str_replace('_', ' ', $user->role ?? 'user'));
                        $isSelf = $user->id === auth()->id();
                        $initials = collect(explode(' ', $user->name ?: 'U'))->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                    @endphp
                    <tr>
                        <td data-export="{{ $user->name }}">
                            <div class="flex items-center gap-3 min-w-[12rem]">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-[13px] shrink-0 {{ $user->role === 'super_admin' ? 'tone-violet' : ($user->role === 'admin' ? 'tone-blue' : 'tone-slate') }}">{{ $initials }}</span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $user->name }}</a>
                                    <span class="block font-mono text-[10.5px] text-slate-400 mt-0.5">ID #{{ $user->id }}@if($isSelf) · You @endif</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="tel:{{ $user->phone }}" class="block font-semibold text-slate-700 dark:text-slate-200 hover:underline whitespace-nowrap">{{ $user->phone }}</a>
                            @if($user->email)
                                <a href="mailto:{{ $user->email }}" class="block text-[11.5px] text-slate-400 hover:underline max-w-[14rem] truncate">{{ $user->email }}</a>
                            @endif
                        </td>
                        <td data-export="{{ $roleLabel }}">
                            <span class="badge {{ $roleBadge($user->role) }}">{{ $roleLabel }}</span>
                        </td>
                        <td data-order="{{ $user->is_active ? 1 : 0 }}" data-export="{{ $user->is_active ? 'Active' : 'Inactive' }}">
                            @if($isSelf)
                                <span class="badge badge-success badge-dot">Active</span>
                            @else
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="switch {{ $user->is_active ? 'is-on' : '' }}" role="switch" aria-checked="{{ $user->is_active ? 'true' : 'false' }}" title="{{ $user->is_active ? 'Deactivate account' : 'Activate account' }}">
                                        <span class="switch-track"></span><span class="switch-text">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td data-order="{{ $user->created_at?->timestamp }}" data-export="{{ $user->created_at?->format('d M Y') }}" class="whitespace-nowrap">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->created_at?->format('d M Y') }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $user->created_at?->diffForHumans() }}</span>
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                                @if(!$isSelf)
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete “{{ $user->name }}”?" title="Delete"><i class="ph ph-trash"></i></button>
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
        $('#usersTable').DataTable({
            order: [[6, 'desc']],
            columnDefs: [
                { targets: [1, 2], visible: false },   // Phone, email: export-only
                { targets: 0, responsivePriority: 1 },
                { targets: 7, responsivePriority: 2 },
                { targets: 4, responsivePriority: 3 },
                { targets: 5, responsivePriority: 4 }
            ]
        });
    });
</script>
@endpush
