<?php
namespace App\Livewire\Tenant\Fees\Invoices;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Services\InvoiceGenerationService;
use App\Domain\Tenant\Services\PaymentService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public FeeInvoice $invoice;
    public bool $showPaymentForm = false;
    public string $paymentAmount = '';
    public string $paymentMethod = 'cash';
    public string $transactionReference = '';

    public function mount(FeeInvoice $invoice): void
    {
        $this->authorize('view', $invoice);
        $this->invoice = $invoice;
        $this->paymentAmount = (string) $invoice->balance_amount;
    }

    public function refreshLateFee(InvoiceGenerationService $invoiceService): void
    {
        $this->invoice = $invoiceService->refreshLateFee($this->invoice);
        $this->paymentAmount = (string) $this->invoice->balance_amount;
    }

    public function recordPayment(PaymentService $paymentService): void
    {
        $this->authorize('create', \App\Domain\Tenant\Models\FeePayment::class);
        $this->validate(['paymentAmount' => ['required', 'numeric', 'min:0.01'], 'paymentMethod' => ['required', 'in:cash,bank_transfer,upi,cheque,gateway']]);
        try {
            $paymentService->recordPayment($this->invoice, (float) $this->paymentAmount, $this->paymentMethod, auth()->user(), $this->transactionReference ?: null);
            $this->invoice = $this->invoice->fresh(['payments.receipt']);
            $this->showPaymentForm = false;
            $this->paymentAmount = (string) $this->invoice->balance_amount;
            session()->flash('success', 'Payment recorded.');
        } catch (RuntimeException $e) {
            $this->addError('paymentAmount', $e->getMessage());
        }
    }

    public function render() { $this->invoice->load(['student', 'lineItems', 'payments.receipt']); return view('livewire.tenant.fees.invoices.show'); }
}
