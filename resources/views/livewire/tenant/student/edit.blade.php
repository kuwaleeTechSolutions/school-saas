<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Edit Student</h1>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Admission Number *</label><input wire:model="admissionNumber" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('admissionNumber')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Status *</label><select wire:model="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="active">Active</option><option value="inactive">Inactive</option><option value="alumni">Alumni</option><option value="transferred_out">Transferred Out</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700">First Name *</label><input wire:model="firstName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('firstName')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Last Name</label><input wire:model="lastName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.students.show', $student) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Changes</button></div>
    </form>
</div>
