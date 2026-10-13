<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Academics Setup</h1>
    <div class="flex gap-1 mb-6 border-b border-slate-200">
        @foreach (['classes' => 'Classes', 'sections' => 'Sections', 'subjects' => 'Subjects'] as $key => $label)
            <button wire:click="$set('tab', '{{ $key }}')" @class(['px-4 py-2 text-sm font-medium border-b-2 -mb-px', 'border-teal-700 text-teal-700' => $tab === $key, 'border-transparent text-slate-500' => $tab !== $key])>{{ $label }}</button>
        @endforeach
    </div>
    @if ($tab === 'classes')
        <form wire:submit="addClass" class="flex gap-3 mb-5"><input wire:model="newClassName" type="text" placeholder="e.g. Grade 4" class="flex-1 rounded-lg border-slate-300 text-sm"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Add Class</button></form>
        @error('newClassName') <p class="text-red-600 text-sm mb-3">{{ $message }}</p> @enderror
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Class</th><th class="px-4 py-3 text-left font-medium text-slate-600">Sections</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($classes as $class)
                        <tr wire:key="class-{{ $class->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $class->name }}</td><td class="px-4 py-3 text-slate-600">{{ $class->sections_count }}</td><td class="px-4 py-3 text-right"><button wire:click="deleteClass({{ $class->id }})" wire:confirm="Delete?" class="text-red-600 hover:text-red-800">Delete</button></td></tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">No classes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
    @if ($tab === 'sections')
        <form wire:submit="addSection" class="flex gap-3 mb-5">
            <select wire:model="sectionClassId" class="rounded-lg border-slate-300 text-sm"><option value="">Select class&hellip;</option>@foreach ($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select>
            <input wire:model="newSectionName" type="text" placeholder="e.g. A" class="w-24 rounded-lg border-slate-300 text-sm">
            <input wire:model="newSectionCapacity" type="number" placeholder="Capacity" class="w-28 rounded-lg border-slate-300 text-sm">
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Add Section</button>
        </form>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Class</th><th class="px-4 py-3 text-left font-medium text-slate-600">Section</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sections as $section)
                        <tr wire:key="section-{{ $section->id }}"><td class="px-4 py-3 text-slate-600">{{ $section->schoolClass->name }}</td><td class="px-4 py-3 font-medium text-slate-800">{{ $section->name }}</td><td class="px-4 py-3 text-right"><button wire:click="deleteSection({{ $section->id }})" wire:confirm="Delete?" class="text-red-600 hover:text-red-800">Delete</button></td></tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">No sections yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
    @if ($tab === 'subjects')
        <form wire:submit="addSubject" class="flex gap-3 mb-5"><input wire:model="newSubjectName" type="text" placeholder="e.g. Mathematics" class="flex-1 rounded-lg border-slate-300 text-sm"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Add Subject</button></form>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Subject</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($subjects as $subject)
                        <tr wire:key="subject-{{ $subject->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $subject->name }}</td><td class="px-4 py-3 text-right"><button wire:click="deleteSubject({{ $subject->id }})" wire:confirm="Delete?" class="text-red-600 hover:text-red-800">Delete</button></td></tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-10 text-center text-slate-500">No subjects yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
