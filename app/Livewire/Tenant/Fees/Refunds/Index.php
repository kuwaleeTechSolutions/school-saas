<?php
namespace App\Livewire\Tenant\Fees\Refunds;
use App\Domain\Tenant\Models\FeePayment;
use App\Domain\Tenant\Models\FeeRefund;
use App\Domain\Tenant\Services\RefundService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?int $paymentId = null;
    public string $refundAmount = '';
    public string $refundReason = '';
    public ?int $rejectingRefundId = null;
    public string $rejectionReason = '';

    public function mount(): void { $this->authorize('viewAny', FeeRefund::class); }

    public function requestRefund(RefundService $refundService): void
    {
        $this->authorize('request', FeeRefund::class);
        $this->validate(['paymentId' => ['required', 'exists:fee_payments,id'], 'refundAmount' => ['required', 'numeric', 'min:0.01'], 'refundReason' => ['required', 'string', 'max:500']]);
        try {
            $payment = FeePayment::findOrFail($this->paymentId);
            $refundService->request($payment, (float) $this->refundAmount, $this->refundReason, auth()->user());
            $this->reset(['paymentId', 'refundAmount', 'refundReason']);
            session()->flash('success', 'Refund requested.');
        } catch (RuntimeException $e) {
            $this->addError('refundAmount', $e->getMessage());
        }
    }

    public function approve(int $refundId, RefundService $refundService): void
    {
        $refund = FeeRefund::findOrFail($refundId);
        $this->authorize('approve', $refund);
        try {
            $refundService->approveAndProcess($refund, auth()->user());
            session()->flash('success', 'Refund approved and processed.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function startReject(int $refundId): void
    {
        $refund = FeeRefund::findOrFail($refundId);
        $this->authorize('reject', $refund);
        $this->rejectingRefundId = $refundId;
    }

    public function confirmReject(RefundService $refundService): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'max:500']]);
        $refund = FeeRefund::findOrFail($this->rejectingRefundId);
        $this->authorize('reject', $refund);
        $refundService->reject($refund, $this->rejectionReason, auth()->user());
        $this->reset(['rejectingRefundId', 'rejectionReason']);
        session()->flash('success', 'Refund rejected.');
    }

    public function render()
    {
        return view('livewire.tenant.fees.refunds.index', [
            'refunds' => FeeRefund::with('payment.invoice.student')->orderByDesc('created_at')->get(),
            'payments' => FeePayment::where('status', 'success')->with('invoice.student')->orderByDesc('paid_at')->limit(50)->get(),
        ]);
    }
}
