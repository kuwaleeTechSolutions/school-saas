<?php
namespace App\Jobs;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePayslipPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $payslipId) {}
    public function handle(TenantContext $tenant): void
    {
        $payslip = Payslip::withoutGlobalScopes()->with('staffProfile.user', 'payrollRun')->findOrFail($this->payslipId);
        $tenant->set($payslip->school);
        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.payslip', ['payslip' => $payslip]);
            $path = "schools/{$payslip->school_id}/payslips/{$payslip->ulid}.pdf";
            Storage::disk('local')->put($path, $pdf->output());
            $payslip->update(['pdf_path' => $path, 'pdf_status' => 'generated']);
        } catch (\Throwable $e) {
            $payslip->update(['pdf_status' => 'failed']);
            throw $e;
        }
    }
}
