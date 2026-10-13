<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Users</h1>
    @can('create', \App\Models\User::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Invite a new user</h2>
            <form wire:submit="invite" class="flex flex-wrap gap-3 items-start">
                <div><input wire:model="newName" type="text" placeholder="Full name" class="rounded-lg border-slate-300 text-sm">@error('newName')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><input wire:model="newEmail" type="email" placeholder="Email" class="rounded-lg border-slate-300 text-sm">@error('newEmail')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><select wire:model="newRoleId" class="rounded-lg border-slate-300 text-sm"><option value="">Select role&hellip;</option>@foreach ($assignableRoles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select>@error('newRoleId')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Invite</button>
            </form>
        </div>
    @endcan
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search users&hellip;" class="mb-4 w-full rounded-lg border-slate-300 text-sm">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Name</th><th class="px-4 py-3 text-left font-medium text-slate-600">Email</th><th class="px-4 py-3 text-left font-medium text-slate-600">Role(s)</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td><td class="px-4 py-3 text-slate-600">{{ $user->email }}</td><td class="px-4 py-3">@foreach ($user->roles as $role)<span class="inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700 mr-1">{{ $role->name }}</span>@endforeach</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
