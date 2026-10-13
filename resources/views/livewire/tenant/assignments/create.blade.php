<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Assignment</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Class / Section *</label><select wire:model.live="sectionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach</select>@error('sectionId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Subject *</label><select wire:model="subjectId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select>@error('subjectId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Title *</label><input wire:model="title" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Instructions</label><textarea wire:model="instructions" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></div>
        <div class="grid grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Max Marks *</label><input wire:model="maxMarks" type="number" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Assigned *</label><input wire:model="assignedDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Due *</label><input wire:model="dueDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('dueDate') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.assignments.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Create</button></div>
    </form>
</div>
