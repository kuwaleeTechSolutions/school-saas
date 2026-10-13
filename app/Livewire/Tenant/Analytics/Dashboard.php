<?php
namespace App\Livewire\Tenant\Analytics;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Services\AnalyticsService;
use Livewire\Attributes\Layout;
use Livewire\Component;
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function mount(EntitlementServiceContract $entitlements): void { abort_unless($entitlements->can('analytics.view'), 403); }
    public function render(AnalyticsService $analytics, EntitlementServiceContract $entitlements)
    {
        abort_unless($entitlements->can('analytics.view'), 403);
        return view('livewire.tenant.analytics.dashboard', [
            'attendance' => $analytics->attendanceTrend(14), 'fees' => $analytics->feeCollectionByMonth(6),
            'distribution' => $analytics->studentDistribution(), 'funnel' => $analytics->admissionsFunnel(),
        ]);
    }
}
