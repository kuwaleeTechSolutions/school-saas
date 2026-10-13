<?php
namespace App\Jobs;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\IssuedCertificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateCertificatePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $certificateId) {}
    public function handle(TenantContext $tenant): void
    {
        $certificate = IssuedCertificate::withoutGlobalScopes()->with('template')->findOrFail($this->certificateId);
        $tenant->set($certificate->school);
        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.certificate', ['certificate' => $certificate]);
            $path = "schools/{$certificate->school_id}/certificates/{$certificate->ulid}.pdf";
            Storage::disk('local')->put($path, $pdf->output());
            $certificate->update(['pdf_path' => $path, 'pdf_status' => 'generated']);
        } catch (\Throwable $e) {
            $certificate->update(['pdf_status' => 'failed']);
            throw $e;
        }
    }
}
