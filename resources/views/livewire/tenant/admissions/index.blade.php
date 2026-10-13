<div>
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-lg font-semibold text-slate-800">Admissions</h1><p class="text-sm text-slate-500">{{ $applications->total() }} applications</p></div>
        @can('create', \App\Domain\Tenant\Models\AdmissionApplication::class)<a href="{{ route('tenant.admissions.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ New Application</a>@endcan
    </div>
    <div class="flex gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name&hellip;" class="flex-1 rounded-lg border-slate-300 text-sm">
        <select wire:model.live="stageFilter" class="rounded-lg border-slate-300 text-sm"><option value="">All stages</option>@foreach (['enquiry','application','submitted','under_review','document_verification','entrance_test','interview','selected','waitlisted','rejected','fee_pending','admitted','cancelled'] as $stage)<option value="{{ $stage }}">{{ str($stage)->headline() }}</option>@endforeach</select>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Applicant</th><th class="px-4 py-3 text-left font-medium text-slate-600">Applying For</th><th class="px-4 py-3 text-left font-medium text-slate-600">Guardian</th><th class="px-4 py-3 text-left font-medium text-slate-600">Stage</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($applications as $app)
                    <tr wire:key="app-{{ $app->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $app->applicantFullName() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $app->applying_for_class }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $app->guardian_name }}</td>
                        <td class="px-4 py-3">{{ str($app->stage)->headline() }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('tenant.admissions.show', $app) }}" class="text-slate-500 hover:text-slate-800">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $applications->links() }}</div>
</div>
