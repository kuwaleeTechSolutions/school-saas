<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-1">Edit Teacher</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $teacher->user->name }}</p>
    <form wire:submit="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Employee Code</label><input wire:model="employeeCode" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Status</label><select wire:model="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="active">Active</option><option value="on_leave">On Leave</option><option value="inactive">Inactive</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700">Qualification</label><input wire:model="qualification" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Specialization</label><input wire:model="specialization" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.teachers.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Changes</button></div>
    </form>
</div>
