{{-- Shared role form. Expects $permissions (grouped by group), optional $role and $rolePermissions. --}}
@php
    $role = $role ?? null;
    $selected = array_map('intval', (array) old('permissions', $rolePermissions ?? []));
    $groupMeta = [
        'catalog'  => ['Catalog',            'package',               'Products, categories, stock and banners'],
        'sales'    => ['Sales',              'shopping-cart-simple',  'Orders, invoices and offers'],
        'content'  => ['Content',            'article',               'Static storefront pages'],
        'security' => ['Users & security',   'shield-check',          'Staff accounts, roles and permissions'],
        'settings' => ['Settings',           'gear-six',              'Theme and system configuration'],
    ];
    $totalPerms = $permissions->flatten()->count();
    $isSuper = $role && $role->name === 'super_admin';
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
    <div class="xl:col-span-2 space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-identification-card"></i> Role details</h3>
                    <p class="card-subtitle">{{ $role ? 'The role key cannot be changed after creation.' : 'The key is used internally; it is saved in lower case with underscores.' }}</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    @if($role)
                        <label class="form-label">Role key</label>
                        <input type="text" value="{{ $role->name }}" disabled class="form-control font-mono opacity-70 cursor-not-allowed">
                    @else
                        <label for="roleName" class="form-label">Role key <span class="text-rose-500">*</span></label>
                        <input type="text" id="roleName" name="name" value="{{ old('name') }}" placeholder="e.g. inventory_manager" required maxlength="50" class="form-control font-mono{{ $errors->has('name') ? ' !border-rose-400' : '' }}">
                        @error('name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    @endif
                </div>
                <div>
                    <label for="displayName" class="form-label">Display name <span class="text-rose-500">*</span></label>
                    <input type="text" id="displayName" name="display_name" value="{{ old('display_name', $role->display_name ?? '') }}" placeholder="e.g. Inventory manager" required maxlength="100" class="form-control{{ $errors->has('display_name') ? ' !border-rose-400' : '' }}">
                    @error('display_name')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="roleDescription" class="form-label">Description</label>
                    <input type="text" id="roleDescription" name="description" value="{{ old('description', $role->description ?? '') }}" placeholder="What this role is responsible for" maxlength="255" class="form-control{{ $errors->has('description') ? ' !border-rose-400' : '' }}">
                    @error('description')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-key"></i> Permission matrix</h3>
                    <p class="card-subtitle">Grant access module by module. Use the group toggle to select every permission in a group.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-ghost btn-sm" data-perm-all="1"><i class="ph ph-check-square"></i> Select all</button>
                    <button type="button" class="btn btn-ghost btn-sm" data-perm-all="0"><i class="ph ph-square"></i> Clear</button>
                </div>
            </div>
            <div class="card-body space-y-4">
                @if($isSuper)
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-violet-50 border border-violet-200 text-violet-800 dark:bg-violet-500/10 dark:border-violet-500/30 dark:text-violet-200 text-[12.5px]">
                        <i class="ph-fill ph-crown text-lg shrink-0"></i>
                        <p>Super administrators bypass permission checks and always have full access. The selections below are stored but not enforced for this role.</p>
                    </div>
                @endif
                @error('permissions')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                @error('permissions.*')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror

                @foreach($permissions as $group => $groupPerms)
                    @php
                        [$gLabel, $gIcon, $gHint] = $groupMeta[$group] ?? [ucfirst($group), 'squares-four', ucfirst($group).' module'];
                        $gId = 'perm-group-'.\Illuminate\Support\Str::slug($group);
                        $gSelected = $groupPerms->filter(fn ($p) => in_array($p->id, $selected))->count();
                    @endphp
                    <fieldset class="perm-group rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden" data-perm-group="{{ $gId }}">
                        <legend class="sr-only">{{ $gLabel }}</legend>
                        <div class="flex flex-wrap items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                            <span class="w-9 h-9 rounded-xl tone-slate flex items-center justify-center text-lg shrink-0"><i class="ph-duotone ph-{{ $gIcon }}"></i></span>
                            <div class="min-w-0 mr-auto">
                                <p class="text-[13px] font-bold text-slate-900 dark:text-white">{{ $gLabel }}</p>
                                <p class="text-[11.5px] text-slate-500 dark:text-slate-400">{{ $gHint }}</p>
                            </div>
                            <span class="badge badge-neutral" data-group-count>{{ $gSelected }} / {{ $groupPerms->count() }}</span>
                            <label class="inline-flex items-center gap-2 text-[12px] font-bold text-slate-700 dark:text-slate-200 cursor-pointer select-none">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-perm-toggle {{ $gSelected === $groupPerms->count() ? 'checked' : '' }}>
                                Select all
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3.5">
                            @foreach($groupPerms as $perm)
                                <label class="perm-tile flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600 transition">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ in_array($perm->id, $selected) ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-perm>
                                    <span class="min-w-0">
                                        <span class="block text-[12.5px] font-bold text-slate-800 dark:text-slate-100">{{ $perm->display_name }}</span>
                                        <span class="block font-mono text-[10.5px] text-slate-400">{{ $perm->name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-chart-pie-slice"></i> Access summary</h3>
            </div>
            <div class="card-body space-y-3">
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-extrabold text-slate-900 dark:text-white"><span id="permSelectedCount">{{ count(array_intersect($selected, $permissions->flatten()->pluck('id')->all())) }}</span><span class="text-base text-slate-400 font-bold"> / {{ $totalPerms }}</span></span>
                    <span class="text-xs font-semibold text-slate-500">permissions granted</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden"><div id="permProgress" class="h-full rounded-full bg-emerald-500 transition-all" style="width: 0%"></div></div>
                <ul class="space-y-1.5 text-[12.5px]" id="permGroupSummary">
                    @foreach($permissions as $group => $groupPerms)
                        <li class="flex justify-between gap-2 text-slate-600 dark:text-slate-300" data-summary-for="perm-group-{{ \Illuminate\Support\Str::slug($group) }}">
                            <span>{{ ($groupMeta[$group][0] ?? ucfirst($group)) }}</span>
                            <span class="font-bold" data-summary-count></span>
                        </li>
                    @endforeach
                </ul>
                @if($role)
                    <p class="form-hint">{{ $role->users()->count() }} {{ \Illuminate\Support\Str::plural('user', $role->users()->count()) }} currently assigned to this role.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body flex gap-2.5">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline flex-1 justify-center">Cancel</a>
                <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph ph-check"></i> {{ $role ? 'Update role' : 'Save role' }}</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .perm-tile:has(input:checked) { border-color: #10b981; background: rgba(16,185,129,.06); }
    .dark .perm-tile:has(input:checked) { border-color: rgba(16,185,129,.55); background: rgba(16,185,129,.1); }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const total = document.querySelectorAll('[data-perm]').length;

        function refresh() {
            let all = 0;
            document.querySelectorAll('[data-perm-group]').forEach(function (g) {
                const boxes = g.querySelectorAll('[data-perm]');
                const on = g.querySelectorAll('[data-perm]:checked').length;
                all += on;
                const toggle = g.querySelector('[data-perm-toggle]');
                toggle.checked = on === boxes.length && boxes.length > 0;
                toggle.indeterminate = on > 0 && on < boxes.length;
                g.querySelector('[data-group-count]').textContent = on + ' / ' + boxes.length;
                const s = document.querySelector('[data-summary-for="' + g.getAttribute('data-perm-group') + '"] [data-summary-count]');
                if (s) s.textContent = on + ' / ' + boxes.length;
            });
            document.getElementById('permSelectedCount').textContent = all;
            document.getElementById('permProgress').style.width = (total ? Math.round(all / total * 100) : 0) + '%';
        }

        document.querySelectorAll('[data-perm-toggle]').forEach(function (t) {
            t.addEventListener('change', function () {
                t.closest('[data-perm-group]').querySelectorAll('[data-perm]').forEach(b => { b.checked = t.checked; });
                refresh();
            });
        });
        document.querySelectorAll('[data-perm]').forEach(b => b.addEventListener('change', refresh));
        document.querySelectorAll('[data-perm-all]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const on = btn.getAttribute('data-perm-all') === '1';
                document.querySelectorAll('[data-perm]').forEach(b => { b.checked = on; });
                refresh();
            });
        });
        refresh();
    });
</script>
@endpush
