<?php
namespace App\Livewire\Tenant;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function mount(TenantContext $tenant): void
    {
        if ($tenant->school()->onboarding_status !== 'completed') $this->redirect(route('tenant.onboarding'), navigate: true);
    }
    public function render(TenantContext $tenant, EntitlementServiceContract $entitlements)
    {
        $school = $tenant->school();
        $subscription = $school->activeSubscription()->with('plan')->first();
        $enabledModules = $subscription ? $subscription->plan->modules()->orderBy('sort_order')->get() : collect();
        return view('livewire.tenant.dashboard', ['school'=>$school,'subscription'=>$subscription,'enabledModules'=>$enabledModules]);
    }
}
