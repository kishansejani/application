@extends('admin.layouts.admin')

@section('title', 'Add New Role')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create Custom Role</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Define role name and select module permissions.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-sm">
            Back to Roles
        </a>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Role Key / Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. inventory_manager" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Display Title *</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" placeholder="e.g. Inventory Manager" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Role responsibilities..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <!-- Permissions Matrix Grouped by Module -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Module Permissions Matrix</h2>

            @foreach($permissions as $group => $groupPerms)
            <div class="border border-slate-100 dark:border-slate-700 rounded-xl p-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">{{ ucfirst($group) }} Module</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($groupPerms as $perm)
                    <label class="flex items-center gap-2 p-2.5 bg-slate-50 dark:bg-slate-900 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="rounded text-black focus:ring-black">
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $perm->display_name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 btn-theme-primary font-bold rounded-xl text-xs shadow-md transition active:scale-95 flex items-center gap-2">
                <i class="fas fa-check"></i>
                <span>Save Role</span>
            </button>
        </div>
    </form>
</div>
@endsection
