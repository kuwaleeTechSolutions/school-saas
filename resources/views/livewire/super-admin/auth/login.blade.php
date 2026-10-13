<div class="w-full max-w-sm mx-auto">
    <div class="mb-8 text-center"><h1 class="text-xl font-semibold text-slate-800">Super Admin</h1><p class="text-sm text-slate-500 mt-1">Platform administration console</p></div>
    <form wire:submit="login" class="space-y-5">
        <div><label class="block text-sm font-medium text-slate-700">Email</label><input wire:model="email" type="email" autofocus class="mt-1 block w-full rounded-lg border-slate-300 text-sm">@error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Password</label><input wire:model="password" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">@error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror</div>
        <label class="flex items-center gap-2 text-sm text-slate-600"><input wire:model="remember" type="checkbox" class="rounded border-slate-300"> Remember me</label>
        <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Sign in</button>
    </form>
</div>
