<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Roles</h1>@can('create', \Spatie\Permission\Models\Role::class)<a href="{{ route('tenant.roles.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ Create Custom Role</a>@endcan</div>
    <h2 class="text-sm font-semibold text-slate-700 mb-3">System Roles</h2>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm mb-8">
        <table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Role</th><th class="px-4 py-3 text-left font-medium text-slate-600">Permissions</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($systemRoles as $role)<tr><td class="px-4 py-3 font-medium text-slate-800">{{ $role->name }}</td><td class="px-4 py-3 text-slate-600">{{ $role->permissions_count }} permissions</td></tr>@endforeach</tbody></table>
    </div>
    <h2 class="text-sm font-semibold text-slate-700 mb-3">Your Custom Roles</h2>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Role</th><th class="px-4 py-3 text-left font-medium text-slate-600">Permissions</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($customRoles as $role)<tr><td class="px-4 py-3 font-medium text-slate-800">{{ $role->name }}</td><td class="px-4 py-3 text-slate-600">{{ $role->permissions_count }} permissions</td></tr>@empty<tr><td colspan="2" class="px-4 py-10 text-center text-slate-500">No custom roles yet.</td></tr>@endforelse</tbody></table>
    </div>
</div>
