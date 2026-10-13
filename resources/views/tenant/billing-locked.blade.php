<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Subscription required</title>@vite(['resources/css/app.css'])</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md text-center">
        <h1 class="text-lg font-semibold text-slate-800">Subscription inactive</h1>
        <p class="mt-2 text-sm text-slate-500">Status: <strong>{{ str(session('subscription_status', 'pending'))->headline() }}</strong>. Your data is safe.</p>
        <a href="{{ route('tenant.billing.plans') }}" class="mt-6 inline-block rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">View Plans</a>
    </div>
</body>
</html>
