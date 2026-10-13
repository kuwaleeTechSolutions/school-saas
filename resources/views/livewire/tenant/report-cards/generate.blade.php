<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Generate Report Cards</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="flex gap-3 items-end">
            <div class="flex-1"><label class="block text-sm font-medium text-slate-700">Exam</label><select wire:model.live="examId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($exams as $exam)<option value="{{ $exam->id }}">{{ $exam->name }} &middot; {{ $exam->schoolClass->name }}</option>@endforeach</select>@error('examId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <button wire:click="generateForClass" wire:confirm="Generate report cards?" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Generate for Class</button>
        </div>
    </div>
    @if ($reportCards->isNotEmpty())
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Student</th><th class="px-4 py-3 text-left font-medium text-slate-600">Overall</th><th class="px-4 py-3 text-left font-medium text-slate-600">PDF Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($reportCards as $card)
                        <tr wire:key="card-{{ $card->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $card->student->fullName() }}</td><td class="px-4 py-3 text-slate-600">{{ $card->overall_percent }}% &middot; {{ $card->overall_grade }}</td><td class="px-4 py-3">{{ str($card->pdf_status)->headline() }}</td><td class="px-4 py-3 text-right"><a href="{{ route('tenant.report-cards.show', $card) }}" class="text-slate-500 hover:text-slate-800">View</a></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
