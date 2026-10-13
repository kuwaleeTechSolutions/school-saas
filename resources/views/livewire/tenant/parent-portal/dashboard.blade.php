<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">My Children</h1>

    <div class="space-y-6">
        @foreach ($children as $child)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-semibold text-slate-800">{{ $child['student']->fullName() }}</h2>
                        <p class="text-sm text-slate-500">{{ $child['class_section'] }}</p>
                    </div>
                    <a href="{{ route('tenant.students.show', $child['student']) }}" class="text-xs text-teal-700 hover:underline">View Profile</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-xs uppercase text-slate-500">Attendance (30d)</div>
                        <div class="mt-1 text-lg font-semibold text-slate-800">{{ $child['attendance_percent'] !== null ? $child['attendance_percent'] . '%' : '—' }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-xs uppercase text-slate-500">Homework Due</div>
                        <div class="mt-1 text-lg font-semibold text-slate-800">{{ $child['pending_homework'] }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-xs uppercase text-slate-500">Fees Outstanding</div>
                        <div class="mt-1 text-lg font-semibold {{ $child['outstanding_fees'] > 0 ? 'text-amber-700' : 'text-slate-800' }}">{{ number_format($child['outstanding_fees'], 2) }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-xs uppercase text-slate-500">Latest Report Card</div>
                        @if ($child['latest_report_card'])
                            <a href="{{ route('tenant.report-cards.show', $child['latest_report_card']) }}" class="mt-1 block text-sm font-semibold text-teal-700 hover:underline">{{ $child['latest_report_card']->overall_grade ?? 'View' }}</a>
                        @else
                            <div class="mt-1 text-sm text-slate-400">None yet</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Recent Announcements</h2>
        <div class="space-y-2">
            @forelse ($announcements as $announcement)
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm">
                    <div class="font-medium text-slate-800">{{ $announcement->title }}</div>
                    <div class="text-slate-600 mt-1">{{ str($announcement->body)->limit(150) }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No announcements yet.</p>
            @endforelse
        </div>
        <a href="{{ route('tenant.announcements.index') }}" class="inline-block mt-3 text-xs text-teal-700 hover:underline">View all announcements &rarr;</a>
    </div>
</div>
