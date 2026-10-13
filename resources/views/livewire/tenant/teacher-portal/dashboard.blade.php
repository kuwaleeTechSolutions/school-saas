<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">My Teaching Dashboard</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Today's Timetable</h2>
            @forelse ($todaysTimetable as $entry)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                    <div>
                        <span class="font-medium text-slate-800">{{ $entry->period->name }}</span>
                        <span class="text-slate-500"> &middot; {{ $entry->subject->name }}</span>
                    </div>
                    <span class="text-slate-500">{{ $entry->timetable->schoolClass->name }} - {{ $entry->timetable->section->name }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No periods scheduled for today.</p>
            @endforelse
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Homework Awaiting Grading</h2>
            @forelse ($pendingGrading as $hw)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                    <span class="text-slate-800">{{ $hw->title }}</span>
                    <a href="{{ route('tenant.homework.show', $hw) }}" class="text-teal-700 hover:underline">{{ $hw->ungraded_count }} to grade</a>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nothing awaiting grading. &#127881;</p>
            @endforelse
        </div>
    </div>

    @if ($classTeacherOf->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-8">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Class Teacher Of</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($classTeacherOf as $section)
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs text-teal-700">{{ $section->schoolClass->name }} - {{ $section->name }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">My Assignments</h2>
        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Class / Section</th><th class="px-3 py-2 text-left font-medium text-slate-600">Subject</th><th class="px-3 py-2 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td class="px-3 py-2 text-slate-700">{{ $assignment->schoolClass->name }}{{ $assignment->section ? ' - ' . $assignment->section->name : '' }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $assignment->subject->name }}</td>
                            <td class="px-3 py-2 text-right"><a href="{{ route('tenant.attendance.take') }}" class="text-teal-700 hover:underline">Take Attendance</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">No assignments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
