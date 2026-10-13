<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Plans</title>@vite(['resources/css/app.css'])</head>
<body class="bg-slate-50 min-h-screen px-4 py-16">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-lg font-semibold text-slate-800 mb-6 text-center">Available Plans</h1>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach (\App\Domain\Platform\Models\Plan::with('prices')->where('status', 'active')->orderBy('display_order')->get() as $plan)
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm text-center">
                    <h2 class="font-semibold text-slate-800">{{ $plan->name }}</h2>
                    @php $price = $plan->prices->firstWhere('billing_cycle', 'monthly'); @endphp
                    <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $price ? $price->currency . ' ' . number_format((float) $price->price) : '—' }} <span class="text-sm font-normal text-slate-500">/mo</span></p>
                    <p class="mt-4 text-xs text-slate-500">{{ $plan->description }}</p>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
