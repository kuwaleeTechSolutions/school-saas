<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-1">Payment Gateway Settings</h1>
    <p class="text-sm text-slate-500 mb-6">Credentials are encrypted at rest. Webhook signature verification works once a webhook secret is saved; creating checkout sessions additionally requires installing the provider's official SDK.</p>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    <div class="space-y-4">
    @foreach ($providers as $provider)
        @php $credential = $credentials->get($provider); @endphp
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3"><h2 class="font-semibold text-slate-800">{{ ucfirst($provider) }}</h2>
                <div class="flex items-center gap-3">
                    @if ($credential?->is_active)<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Active</span>
                    @elseif ($credential?->isConfigured())<button wire:click="toggleActive('{{ $provider }}')" class="text-xs text-teal-700 hover:underline">Make Active</button>@endif
                    <button wire:click="edit('{{ $provider }}')" class="text-xs text-slate-500 hover:underline">{{ $credential ? 'Update' : 'Configure' }} Credentials</button>
                </div></div>
            <p class="text-xs text-slate-400">{{ $credential?->isConfigured() ? 'API credentials configured.' : 'Not yet configured.' }}</p>
            @if ($editingProvider === $provider)
                <form wire:submit="save" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                    <div><label class="block text-xs font-medium text-slate-700">API Key *</label><input wire:model="apiKey" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('apiKey')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-xs font-medium text-slate-700">API Secret *</label><input wire:model="apiSecret" type="password" autocomplete="off" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('apiSecret')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-xs font-medium text-slate-700">Webhook Secret</label><input wire:model="webhookSecret" type="password" autocomplete="off" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                    <label class="flex items-center gap-2 text-xs text-slate-600"><input wire:model="isTestMode" type="checkbox" class="rounded border-slate-300"> Test mode</label>
                    <div class="flex justify-end gap-2"><button type="button" wire:click="$set('editingProvider', '')" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700">Cancel</button><button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs text-white">Save</button></div>
                </form>
            @endif
        </div>
    @endforeach
    </div>
</div>
