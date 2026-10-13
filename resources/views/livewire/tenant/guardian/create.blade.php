<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Add Guardian</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Name *</label><input wire:model="name" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Phone *</label><input wire:model="phone" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Email</label><input wire:model="email" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div class="pt-4 border-t border-slate-100">
            <label class="block text-sm font-medium text-slate-700 mb-2">Link to Student(s)</label>
            <select wire:model="linkedStudentIds" multiple class="w-full rounded-lg border-slate-300 text-sm" size="5">@foreach ($students as $student)<option value="{{ $student->id }}">{{ $student->fullName() }}</option>@endforeach</select>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.guardians.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Guardian</button></div>
    </form>
</div>
