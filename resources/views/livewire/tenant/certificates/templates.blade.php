<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Certificate Templates</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\CertificateTemplate::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">New Template</h2>
            <form wire:submit="save" class="space-y-3">
                <input wire:model="name" type="text" placeholder="Template name" class="w-full rounded-lg border-slate-300 text-sm">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <select wire:model="type" class="w-full rounded-lg border-slate-300 text-sm"><option value="bonafide">Bonafide</option><option value="transfer">Transfer</option><option value="character">Character</option><option value="other">Other</option></select>
                <textarea wire:model="bodyTemplate" rows="4" class="w-full rounded-lg border-slate-300 text-sm font-mono text-xs"></textarea>
                @error('bodyTemplate') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400">Available placeholders: {{ '{{student_name}}' }}, {{ '{{admission_number}}' }}, {{ '{{class_section}}' }}, {{ '{{date_of_birth}}' }}, {{ '{{issue_date}}' }}</p>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Template</button>
            </form>
        </div>
    @endcan
    <div class="space-y-2">
        @forelse ($templates as $template)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="font-medium text-slate-800">{{ $template->name }}</div><div class="text-xs text-slate-500">{{ str($template->type)->headline() }}</div></div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">No templates yet.</div>
        @endforelse
    </div>
</div>
