<?php
namespace App\Livewire\SuperAdmin\UsageDashboard;
use App\Domain\Platform\Services\UsageMeteringService;
use Livewire\Attributes\Layout;
use Livewire\Component;
#[Layout('components.layouts.super-admin')]
class Index extends Component
{
    public function render(UsageMeteringService $usage)
    {
        return view('livewire.super-admin.usage-dashboard.index', ['platformTotals'=>$usage->platformTotals(),'schoolsNearingLimits'=>$usage->schoolsNearingLimits(),'usageService'=>$usage]);
    }
}
