<?php
namespace App\Livewire\Tenant\Fees\Reports;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Services\FinancialReportService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public string $fromDate;
    public string $toDate;

    public function mount(EntitlementServiceContract $entitlements): void
    {
        abort_unless($entitlements->can('fees.view'), 403);
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function render(FinancialReportService $reports)
    {
        $from = Carbon::parse($this->fromDate);
        $to = Carbon::parse($this->toDate);
        return view('livewire.tenant.fees.reports.dashboard', [
            'collections' => $reports->collectionsBetween($from, $to),
            'collectionsByMethod' => $reports->collectionsByMethod($from, $to),
            'outstandingTotal' => $reports->outstandingTotal(),
            'overdueCount' => $reports->overdueInvoicesCount(),
            'outstandingInvoices' => $reports->outstandingInvoices()->take(10),
        ]);
    }
}
