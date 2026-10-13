<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Add Staff Member</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Full Name *</label><input wire:model="name" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Email *</label><input wire:model="email" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Employee Code</label><input wire:model="employeeCode" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Joining Date</label><input wire:model="joiningDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Designation</label><input wire:model="designation" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Department</label><input wire:model="department" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Monthly Gross Salary *</label><input wire:model="monthlyGrossSalary" type="number" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('monthlyGrossSalary') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Annual Leave Quota</label><input wire:model="annualLeaveQuota" type="number" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.hr.staff.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Save Staff</button></div>
    </form>
</div>
