<div>
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-lg font-semibold text-slate-800">Students</h1><p class="text-sm text-slate-500">{{ $students->total() }} students</p></div>
        @can('create', \App\Domain\Tenant\Models\Student::class)<a href="{{ route('tenant.students.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ Add Student</a>@endcan
    </div>
    <div class="flex gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search&hellip;" class="flex-1 rounded-lg border-slate-300 text-sm">
        <select wire:model.live="statusFilter" class="rounded-lg border-slate-300 text-sm"><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="alumni">Alumni</option><option value="transferred_out">Transferred Out</option></select>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Admission #</th><th class="px-4 py-3 text-left font-medium text-slate-600">Name</th><th class="px-4 py-3 text-left font-medium text-slate-600">Class / Section</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $student)
                    <tr wire:key="student-{{ $student->id }}">
                        <td class="px-4 py-3 text-slate-600">{{ $student->admission_number }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $student->fullName() }}</td>
                        <td class="px-4 py-3 text-slate-600">@if ($student->currentEnrollment){{ $student->currentEnrollment->schoolClass->name }} - {{ $student->currentEnrollment->section->name }}@else<span class="text-slate-400">Not enrolled</span>@endif</td>
                        <td class="px-4 py-3">{{ str($student->status)->headline() }}</td>
                        <td class="px-4 py-3 text-right space-x-2"><a href="{{ route('tenant.students.show', $student) }}" class="hover:text-slate-800">View</a>@can('update', $student)<a href="{{ route('tenant.students.edit', $student) }}" class="hover:text-slate-800">Edit</a>@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
</div>
