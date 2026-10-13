<?php
use App\Livewire\SuperAdmin\Auth\Login as SuperAdminLogin;
use App\Livewire\SuperAdmin\PackageBuilder\Index as PackageBuilderIndex;
use App\Livewire\SuperAdmin\SchoolLifecycle\Index as SchoolLifecycleIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', SuperAdminLogin::class)->name('super-admin.login');
    });
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', function () {
            Auth::guard('admin')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('super-admin.login');
        })->name('super-admin.logout');
        Route::get('/', fn () => redirect()->route('super-admin.dashboard'));
        Route::get('/dashboard', function () {
            return view('super-admin.dashboard', [
                'totalSchools' => \App\Domain\Tenant\Models\School::count(),
                'activeSchools' => \App\Domain\Tenant\Models\School::where('status', 'active')->count(),
                'trialSchools' => \App\Domain\Tenant\Models\School::where('status', 'trial')->count(),
                'suspendedSchools' => \App\Domain\Tenant\Models\School::where('status', 'suspended')->count(),
            ]);
        })->name('super-admin.dashboard');
        Route::get('/packages', PackageBuilderIndex::class)->name('super-admin.packages.index');
        Route::get('/schools', SchoolLifecycleIndex::class)->name('super-admin.schools.index');

        // Stage 8 - Platform Commerce

        Route::get('/coupons', \App\Livewire\SuperAdmin\Coupons\Index::class)
        ->name('super-admin.coupons.index');

        Route::get('/coupons/create', \App\Livewire\SuperAdmin\Coupons\Create::class)
        ->name('super-admin.coupons.create');

        Route::get('/usage-dashboard', \App\Livewire\SuperAdmin\UsageDashboard\Index::class)
        ->name('super-admin.usage-dashboard.index');

        Route::get('/gateway-settings', \App\Livewire\SuperAdmin\GatewaySettings::class)
        ->name('super-admin.gateway-settings');

        Route::get('/platform-invoices', \App\Livewire\SuperAdmin\PlatformInvoices\Index::class)
        ->name('super-admin.platform-invoices.index');

        Route::get('/platform-invoices/{invoice}', \App\Livewire\SuperAdmin\PlatformInvoices\Show::class)
        ->name('super-admin.platform-invoices.show');


    });
});
