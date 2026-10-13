<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app(\App\Domain\Shared\Tenancy\TenantContext::class)->school()->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 min-h-screen">
    @php
        $school = app(\App\Domain\Shared\Tenancy\TenantContext::class)->school();
        $subscription = $school->activeSubscription()->with('plan.modules')->first();
        $enabledModules = $subscription ? $subscription->plan->modules()->orderBy('sort_order')->get() : collect();
        $builtRoutes = [
            'students' => 'tenant.students.index', 'teachers' => 'tenant.teachers.index',
            'parents' => 'tenant.guardians.index', 'classes_sections' => 'tenant.academics.manage',
            'subjects' => 'tenant.academics.manage', 'admission' => 'tenant.admissions.index',
            'attendance' => 'tenant.attendance.take', 'timetable' => 'tenant.timetable.manage',
            'homework' => 'tenant.homework.index', 'assignments' => 'tenant.assignments.index',
            'examinations' => 'tenant.examinations.index', 'results' => 'tenant.results.approvals',
            'fees' => 'tenant.fees.dashboard',
        ];
    @endphp
    <div class="flex">
        <aside class="w-60 shrink-0 bg-white border-r border-slate-200 min-h-screen overflow-y-auto">
            <div class="px-5 py-5 text-sm font-semibold text-teal-800 border-b border-slate-100">{{ $school->name }}</div>
            <nav class="px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('tenant.dashboard') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Dashboard</a>
                @can('viewAny', \App\Models\User::class)
                    <a href="{{ route('tenant.users.index') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Users</a>
                @endcan
                @can('viewAny', \Spatie\Permission\Models\Role::class)
                    <a href="{{ route('tenant.roles.index') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Roles</a>
                @endcan
                @can('viewAny', \App\Domain\Tenant\Models\AcademicSession::class)
                    <a href="{{ route('tenant.academic-sessions.index') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Academic Sessions</a>
                @endcan
                @can('viewAny', \App\Domain\Tenant\Models\Student::class)
                    <a href="{{ route('tenant.promotion.wizard') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Promotion</a>
                @endcan

                <div class="pt-2 mt-2 border-t border-slate-100 text-xs uppercase tracking-wide text-slate-400 px-3">Modules</div>

                @foreach ($enabledModules as $module)
                    @if (isset($builtRoutes[$module->code]))
                        <a href="{{ route($builtRoutes[$module->code]) }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">{{ $module->name }}</a>
                        @if ($module->code === 'attendance')
                            <a href="{{ route('tenant.attendance.report') }}" class="block rounded-lg px-3 py-2 pl-6 text-xs text-slate-500 hover:bg-slate-100">&rarr; Attendance Report</a>
                        @endif
                        @if ($module->code === 'fees')
                            <a href="{{ route('tenant.fees.structures.index') }}" class="block rounded-lg px-3 py-2 pl-6 text-xs text-slate-500 hover:bg-slate-100">&rarr; Fee Structures</a>
                            <a href="{{ route('tenant.fees.invoices.index') }}" class="block rounded-lg px-3 py-2 pl-6 text-xs text-slate-500 hover:bg-slate-100">&rarr; Invoices</a>
                            <a href="{{ route('tenant.fees.refunds.index') }}" class="block rounded-lg px-3 py-2 pl-6 text-xs text-slate-500 hover:bg-slate-100">&rarr; Refunds</a>
                        @endif
                    @else
                        <span class="block rounded-lg px-3 py-2 text-slate-400 cursor-not-allowed" title="Coming in a later stage">{{ $module->name }}</span>
                    @endif
                @endforeach
            </nav>
        </aside>
        <div class="flex-1 min-h-screen">
            <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between">
                <div></div>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-500 hover:text-slate-800">Sign out ({{ auth()->user()?->name }})</button>
                </form>
            </header>
            <main class="p-6">{{ $slot }}</main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
