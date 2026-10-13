<?php
namespace App\Livewire\Tenant\Fees\Invoices;
use App\Domain\Tenant\Models\FeeInstallment;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Services\InvoiceGenerationService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Generate extends Component
{
    public ?int $feeInstallmentId = null;
    public function mount(): void { $this->authorize('generate', FeeInvoice::class); }

    public function generate(InvoiceGenerationService $invoiceService): void
    {
        $this->validate(['feeInstallmentId' => ['required', 'exists:fee_installments,id']]);
        try {
            $invoices = $invoiceService->generateForInstallmentBulk($this->feeInstallmentId, auth()->user());
            session()->flash('success', count($invoices) . ' invoices generated/confirmed.');
        } catch (RuntimeException $e) {
            $this->addError('feeInstallmentId', $e->getMessage());
        }
    }

    public function render() { return view('livewire.tenant.fees.invoices.generate', ['installments' => FeeInstallment::with('plan.feeStructure')->orderByDesc('due_date')->get()]); }
}
