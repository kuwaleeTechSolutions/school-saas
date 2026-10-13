<?php
namespace App\Livewire\SuperAdmin\PlatformInvoices;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Platform\Services\PlatformPaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
#[Layout('components.layouts.super-admin')]
class Show extends Component
{
    public PlatformInvoice $invoice; public bool $showPaymentForm = false; public string $paymentAmount = ''; public string $paymentMethod = 'bank_transfer'; public string $reference = '';
    public function mount(PlatformInvoice $invoice): void { $this->authorize('viewAny', PlatformInvoice::class); $this->invoice = $invoice; $this->paymentAmount = (string) $invoice->balance_amount; }
    public function recordPayment(PlatformPaymentService $payments): void
    {
        $this->authorize('recordPayment', $this->invoice);
        $this->validate(['paymentAmount'=>['required','numeric','min:0.01'],'paymentMethod'=>['required','in:bank_transfer,cheque']]);
        try {
            $payments->recordManualPayment($this->invoice, (float) $this->paymentAmount, $this->paymentMethod, Auth::guard('admin')->id(), $this->reference ?: null);
            $this->invoice = $this->invoice->fresh(['payments','lineItems']);
            $this->showPaymentForm = false; $this->paymentAmount = (string) $this->invoice->balance_amount;
            session()->flash('success', 'Payment recorded.');
        } catch (RuntimeException $e) { $this->addError('paymentAmount', $e->getMessage()); }
    }
    public function render() { $this->invoice->load(['school','lineItems','payments']); return view('livewire.super-admin.platform-invoices.show'); }
}
