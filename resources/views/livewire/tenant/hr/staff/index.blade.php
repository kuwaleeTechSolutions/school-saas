<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Staff</h1>@can('create', \App\Domain\Tenant\Models\StaffProfile::class)<a href="{{ route('tenant.hr.staff.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">+ Add Staff</a>@endcan</div>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Name</th><th class="px-4 py-3 text-left font-medium text-slate-600">Designation</th><th class="px-4 py-3 text-left font-medium text-slate-600">Department</th><th class="px-4 py-3 text-left font-medium text-slate-600">Monthly Salary</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($staff as $profile)
                    <tr wire:key="staff-{{ $profile->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $profile->user->name }}</td><td class="px-4 py-3 text-slate-600">{{ $profile->designation ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ $profile->department ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ number_format($profile->monthly_gross_salary, 2) }}</td><td class="px-4 py-3">{{ str($profile->status)->headline() }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No staff records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
</div>
