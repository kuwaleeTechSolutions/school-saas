<div class="w-full max-w-sm mx-auto">
    <div class="mb-8 text-center"><h1 class="text-xl font-semibold text-slate-800">{{ app(\App\Domain\Shared\Tenancy\TenantContext::class)->school()->name }}</h1><p class="text-sm text-slate-500 mt-1">Sign in to your school account</p></div>
    <form wire:submit="login" class="space-y-5">
        <div><label class="block text-sm font-medium text-slate-700">Email</label><input wire:model="email" type="email" autofocus class="mt-1 block w-full rounded-lg border-slate-300 text-sm">@error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Password</label><input wire:model="password" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">@error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror</div>
        <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-800">Sign in</button>
    </form>
</div>
