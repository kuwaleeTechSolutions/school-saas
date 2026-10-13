<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Add Teacher</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Full Name *</label><input wire:model="name" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Email *</label><input wire:model="email" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror<p class="text-xs text-slate-400 mt-1">A login will be created; password reset is Stage 6 Communication work.</p></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Employee Code</label><input wire:model="employeeCode" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Joining Date *</label><input wire:model="joiningDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Qualification</label><input wire:model="qualification" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Specialization</label><input wire:model="specialization" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.teachers.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Teacher</button></div>
    </form>
</div>
