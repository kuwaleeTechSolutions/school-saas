<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Add Student</h1>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Admission Number *</label><input wire:model="admissionNumber" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('admissionNumber')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Date of Birth</label><input wire:model="dateOfBirth" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">First Name *</label><input wire:model="firstName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('firstName')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Last Name</label><input wire:model="lastName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Gender</label><select wire:model="gender" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">—</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700">Blood Group</label><input wire:model="bloodGroup" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Admission Date *</label><input wire:model="admissionDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('admissionDate')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Class / Section *</label><select wire:model="sectionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach</select>@error('sectionId')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">House</label><select wire:model="houseId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">—</option>@foreach ($houses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></div>
        </div>
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Primary Guardian</h2>
            <div class="grid grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-slate-700">Name *</label><input wire:model="guardianName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('guardianName')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-slate-700">Phone *</label><input wire:model="guardianPhone" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('guardianPhone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-slate-700">Relation *</label><select wire:model="guardianRelation" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="father">Father</option><option value="mother">Mother</option><option value="guardian">Guardian</option><option value="other">Other</option></select></div>
            </div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.students.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Student</button></div>
    </form>
</div>
