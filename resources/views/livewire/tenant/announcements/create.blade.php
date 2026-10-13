<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Announcement</h1>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-slate-700">Title *</label>
            <input wire:model="title" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Message *</label>
            <textarea wire:model="body" rows="5" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea>
            @error('body') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Audience</h2>
            <select wire:model.live="audienceType" class="w-full rounded-lg border-slate-300 text-sm">
                <option value="school_wide">Entire School</option>
                <option value="class">Specific Class</option>
                <option value="section">Specific Section</option>
                <option value="role">Specific Role</option>
            </select>

            @if ($audienceType === 'class')
                <select wire:model="classId" class="mt-3 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Select class&hellip;</option>
                    @foreach ($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach
                </select>
                @error('classId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            @elseif ($audienceType === 'section')
                <select wire:model="sectionId" class="mt-3 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Select section&hellip;</option>
                    @foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach
                </select>
                @error('sectionId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            @elseif ($audienceType === 'role')
                <select wire:model="role" class="mt-3 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Select role&hellip;</option>
                    @foreach ($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
                </select>
                @error('role') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            @endif
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input wire:model="alsoEmail" type="checkbox" class="rounded border-slate-300"> Also send by email
        </label>

        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route('tenant.announcements.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Publish</button>
        </div>
    </form>
</div>
