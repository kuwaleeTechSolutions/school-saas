<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Library Circulation</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Issue a Book</h2>
        <form wire:submit="issue" class="grid grid-cols-3 gap-3 items-end">
            <div><label class="block text-xs font-medium text-slate-700">Book Copy</label><select wire:model="copyId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($availableCopies as $copy)<option value="{{ $copy->id }}">{{ $copy->book->title }} ({{ $copy->accession_number }})</option>@endforeach</select>@error('copyId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Student</label><select wire:model="studentId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($students as $student)<option value="{{ $student->id }}">{{ $student->fullName() }}</option>@endforeach</select>@error('studentId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Due Date</label><input wire:model="dueOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div class="col-span-3"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Issue Book</button></div>
        </form>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Currently Issued</h2>
        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Book</th><th class="px-3 py-2 text-left font-medium text-slate-600">Student</th><th class="px-3 py-2 text-left font-medium text-slate-600">Due</th><th class="px-3 py-2 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($currentlyOut as $issue)
                        <tr wire:key="issue-{{ $issue->id }}" class="{{ $issue->isOverdue() ? 'bg-red-50' : '' }}"><td class="px-3 py-2 text-slate-700">{{ $issue->copy->book->title }}</td><td class="px-3 py-2 text-slate-700">{{ $issue->student->fullName() }}</td><td class="px-3 py-2 text-slate-600">{{ $issue->due_on->format('d M Y') }} {{ $issue->isOverdue() ? '(Overdue)' : '' }}</td><td class="px-3 py-2 text-right"><button wire:click="returnBook({{ $issue->id }})" class="text-teal-700 hover:underline">Return</button></td></tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">No books currently out.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
