<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Results Approval Queue</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    <div class="mb-4"><select wire:model.live="statusFilter" class="rounded-lg border-slate-300 text-sm"><option value="submitted">Submitted</option><option value="class_teacher_verified">Class Teacher Verified</option><option value="coordinator_verified">Coordinator Verified</option><option value="principal_approved">Principal Approved</option><option value="published">Published</option></select></div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Exam</th><th class="px-4 py-3 text-left font-medium text-slate-600">Subject</th><th class="px-4 py-3 text-left font-medium text-slate-600">Class</th><th class="px-4 py-3 text-right font-medium text-slate-600">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($examSubjects as $es)
                    <tr wire:key="es-{{ $es->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $es->exam->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $es->subject->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $es->exam->schoolClass->name }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($es->status === 'submitted')@can('verify', $es)<button wire:click="advance({{ $es->id }}, 'class_teacher_verified')" class="text-teal-700 hover:text-teal-900">Verify (Class Teacher)</button>@endcan
                            @elseif ($es->status === 'class_teacher_verified')@can('verify', $es)<button wire:click="advance({{ $es->id }}, 'coordinator_verified')" class="text-teal-700 hover:text-teal-900">Verify (Coordinator)</button>@endcan
                            @elseif ($es->status === 'coordinator_verified')@can('verify', $es)<button wire:click="advance({{ $es->id }}, 'principal_approved')" class="text-teal-700 hover:text-teal-900">Approve (Principal)</button>@endcan
                            @elseif ($es->status === 'principal_approved')@can('publish', $es)<button wire:click="advance({{ $es->id }}, 'published')" wire:confirm="Publish?" class="text-emerald-700 hover:text-emerald-900">Publish</button>@endcan
                            @else<span class="text-slate-400">—</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">Nothing in this stage.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
