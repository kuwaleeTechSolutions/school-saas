<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-1">Create Custom Role</h1>
    <p class="text-sm text-slate-500 mb-6">Only permissions included in your current package are shown below.</p>
    <form wire:submit="save" class="space-y-6">
        <div><label class="block text-sm font-medium text-slate-700">Role Name *</label><input wire:model="name" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        <div class="space-y-4">
            @forelse ($availablePermissions as $moduleName => $perms)
                <div class="rounded-lg border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ $moduleName }}</h3>
                    <div class="grid grid-cols-2 gap-2">@foreach ($perms as $perm)<label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" wire:model="selectedPermissionIds" value="{{ $perm->id }}" class="rounded border-slate-300"> {{ $perm->name }}</label>@endforeach</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No permissions are available under your current package.</p>
            @endforelse
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.roles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Create Role</button></div>
    </form>
</div>
