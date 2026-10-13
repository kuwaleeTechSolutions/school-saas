<div class="max-w-lg">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Academic Session</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Session Name *</label><input wire:model="name" type="text" placeholder="2027-28" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Starts On *</label><input wire:model="startsOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('startsOn') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Ends On *</label><input wire:model="endsOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('endsOn') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600"><input wire:model="makeCurrentNow" type="checkbox" class="rounded border-slate-300"> Make this the active session immediately</label>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.academic-sessions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Create Session</button></div>
    </form>
</div>
