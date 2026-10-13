<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="flex">
        <aside class="w-60 shrink-0 bg-slate-900 min-h-screen text-slate-200">
            <div class="px-5 py-5 text-sm font-semibold text-white border-b border-slate-800">{{ config('app.name') }} &middot; Platform</div>
            <nav class="px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('super-admin.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Dashboard</a>
                <a href="{{ route('super-admin.packages.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Packages</a>
                <a href="{{ route('super-admin.schools.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Schools</a>
            </nav>
        </aside>
        <div class="flex-1 min-h-screen">
            <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between">
                <div></div>
                <form method="POST" action="{{ route('super-admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-500 hover:text-slate-800">Sign out ({{ auth('admin')->user()?->name }})</button>
                </form>
            </header>
            <main class="p-6">{{ $slot }}</main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
