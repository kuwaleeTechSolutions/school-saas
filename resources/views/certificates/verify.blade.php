<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate Verification</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full rounded-xl border border-slate-200 bg-white p-8 shadow-sm text-center">
        @if ($certificate)
            <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">&#10003;</div>
            <h1 class="text-lg font-semibold text-slate-800">Certificate Verified</h1>
            <div class="mt-4 text-left text-sm text-slate-600 space-y-1">
                <div><strong>Certificate No:</strong> {{ $certificate->certificate_number }}</div>
                <div><strong>Student:</strong> {{ $certificate->student->fullName() }}</div>
                <div><strong>Issued On:</strong> {{ $certificate->issued_on->format('d M Y') }}</div>
                <div><strong>School:</strong> {{ $certificate->school->name }}</div>
            </div>
        @else
            <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-600">&#10007;</div>
            <h1 class="text-lg font-semibold text-slate-800">Certificate Not Found</h1>
            <p class="mt-2 text-sm text-slate-500">This verification code does not match any issued certificate.</p>
        @endif
    </div>
</body>
</html>
