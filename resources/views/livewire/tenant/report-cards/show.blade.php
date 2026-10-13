<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-lg font-semibold text-slate-800">{{ $reportCard->snapshot['student_name'] }}</h1><p class="text-sm text-slate-500">{{ $reportCard->snapshot['exam_name'] }} &middot; {{ $reportCard->snapshot['class_section'] }}</p></div>
        @if ($reportCard->pdf_status === 'generated')<a href="{{ route('tenant.report-cards.download', $reportCard) }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Download PDF</a>@else<span class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-500">PDF {{ str($reportCard->pdf_status)->headline() }}</span>@endif
    </div>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm mb-6">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Subject</th><th class="px-4 py-3 text-left font-medium text-slate-600">Marks</th><th class="px-4 py-3 text-left font-medium text-slate-600">Grade</th><th class="px-4 py-3 text-left font-medium text-slate-600">Result</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($reportCard->snapshot['subjects'] as $row)
                    <tr><td class="px-4 py-3 font-medium text-slate-800">{{ $row['subject'] }}</td><td class="px-4 py-3 text-slate-600">{{ $row['is_absent'] ? 'Absent' : $row['marks_obtained'] . ' / ' . $row['max_marks'] }}</td><td class="px-4 py-3 text-slate-600">{{ $row['grade'] ?? '—' }}</td><td class="px-4 py-3">{{ $row['is_absent'] ? '—' : ($row['passed'] ? 'Pass' : 'Fail') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6"><div class="text-sm text-slate-600"><strong>Overall:</strong> {{ $reportCard->overall_percent }}% &middot; Grade {{ $reportCard->overall_grade }} &middot; <strong>Attendance:</strong> {{ $reportCard->snapshot['attendance_percent'] ?? '—' }}%</div></div>
    @can('generate', \App\Domain\Tenant\Models\ReportCard::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Remarks</h2>
            <form wire:submit="saveRemarks" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700">Teacher's Remarks</label><textarea wire:model="teacherRemarks" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">Principal's Remarks</label><textarea wire:model="principalRemarks" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></div>
                <div class="flex justify-end"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Remarks</button></div>
            </form>
        </div>
    @endcan
</div>
