<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Admission Application</h1>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Applicant First Name *</label><input wire:model="applicantFirstName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('applicantFirstName') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Applicant Last Name</label><input wire:model="applicantLastName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Date of Birth</label><input wire:model="applicantDateOfBirth" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Gender</label><select wire:model="applicantGender" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">—</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700">Applying For Class *</label><input wire:model="applyingForClass" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('applyingForClass') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Previous School</label><input wire:model="previousSchool" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Guardian</h2>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700">Name *</label><input wire:model="guardianName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('guardianName') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                <div><label class="block text-sm font-medium text-slate-700">Phone *</label><input wire:model="guardianPhone" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('guardianPhone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                <div class="col-span-2"><label class="block text-sm font-medium text-slate-700">Email</label><input wire:model="guardianEmail" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            </div>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">Application Fee</label><input wire:model="applicationFeeAmount" type="number" step="0.01" class="mt-1 w-40 rounded-lg border-slate-300 text-sm"></div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.admissions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Create Application</button></div>
    </form>
</div>
