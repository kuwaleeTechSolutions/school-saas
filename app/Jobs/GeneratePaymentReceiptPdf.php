<?php
namespace App\Jobs;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\PaymentReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePaymentReceiptPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $receiptId) {}
    public function handle(TenantContext $tenant): void
    {
        $receipt = PaymentReceipt::withoutGlobalScopes()->with('payment.invoice.student', 'payment.invoice.lineItems')->findOrFail($this->receiptId);
        $tenant->set($receipt->school);
        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.payment-receipt', ['receipt' => $receipt]);
            $path = "schools/{$receipt->school_id}/receipts/{$receipt->receipt_number}.pdf";
            Storage::disk('local')->put($path, $pdf->output());
            $receipt->update(['pdf_path' => $path, 'pdf_status' => 'generated']);
        } catch (\Throwable $e) {
            $receipt->update(['pdf_status' => 'failed']);
            throw $e;
        }
    }
}
