<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Transport Assignments</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Assign a Student</h2>
        <form wire:submit="assign" class="grid grid-cols-3 gap-3 items-end">
            <div><label class="block text-xs font-medium text-slate-700">Student</label><select wire:model="studentId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($students as $student)<option value="{{ $student->id }}">{{ $student->fullName() }}</option>@endforeach</select>@error('studentId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Route</label><select wire:model="routeId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($routes as $route)<option value="{{ $route->id }}">{{ $route->name }}</option>@endforeach</select>@error('routeId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Stop (optional)</label><select wire:model="stopId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">None</option>@foreach ($routes as $route)@foreach ($route->stops as $stop)<option value="{{ $stop->id }}">{{ $route->name }} &rarr; {{ $stop->name }}</option>@endforeach @endforeach</select></div>
            <div class="col-span-3"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Assign</button></div>
        </form>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Student</th><th class="px-4 py-3 text-left font-medium text-slate-600">Route</th><th class="px-4 py-3 text-left font-medium text-slate-600">Stop</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($assignments as $assignment)
                    <tr wire:key="assign-{{ $assignment->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $assignment->student->fullName() }}</td><td class="px-4 py-3 text-slate-600">{{ $assignment->route->name }}</td><td class="px-4 py-3 text-slate-600">{{ $assignment->stop->name ?? '—' }}</td><td class="px-4 py-3 text-right"><button wire:click="unassign({{ $assignment->student_id }})" wire:confirm="Remove this assignment?" class="text-red-600 hover:underline">Unassign</button></td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No students assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
