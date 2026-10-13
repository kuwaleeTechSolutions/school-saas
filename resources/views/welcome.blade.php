<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>{{ config('app.name') }}</title>@vite(['resources/css/app.css'])</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg text-center">
        <h1 class="text-2xl font-semibold text-slate-800">{{ config('app.name') }}</h1>
        <p class="mt-3 text-sm text-slate-500">Public marketing site placeholder (built in a later stage).</p>
        <div class="mt-6"><a href="{{ route('super-admin.login') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 hover:bg-white text-sm">Super Admin Login</a></div>
    </div>
</body>
</html>
