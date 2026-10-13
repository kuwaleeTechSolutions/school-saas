<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Academic Sessions</h1>@can('create', \App\Domain\Tenant\Models\AcademicSession::class)<a href="{{ route('tenant.academic-sessions.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ New Session</a>@endcan</div>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Session</th><th class="px-4 py-3 text-left font-medium text-slate-600">Period</th><th class="px-4 py-3 text-left font-medium text-slate-600">Classes</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sessions as $session)
                    <tr wire:key="session-{{ $session->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $session->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $session->starts_on->format('d M Y') }} &ndash; {{ $session->ends_on->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $session->classes_count }}</td>
                        <td class="px-4 py-3">@if ($session->is_current)<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700">Current</span>@else<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-600">Inactive</span>@endif</td>
                        <td class="px-4 py-3 text-right">@unless ($session->is_current)<button wire:click="makeCurrent({{ $session->id }})" wire:confirm="Make current?" class="text-teal-700 hover:text-teal-900">Set as Current</button>@endunless</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No sessions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
