<?php
namespace App\Jobs;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\ReportCard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReportCardPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $reportCardId) {}
    public function handle(TenantContext $tenant): void
    {
        $reportCard = ReportCard::withoutGlobalScopes()->findOrFail($this->reportCardId);
        $tenant->set($reportCard->school);
        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.report-card', ['reportCard' => $reportCard->fresh()]);
            $path = "schools/{$reportCard->school_id}/report-cards/{$reportCard->ulid}.pdf";
            Storage::disk('local')->put($path, $pdf->output());
            $reportCard->update(['pdf_path' => $path, 'pdf_status' => 'generated']);
        } catch (\Throwable $e) {
            $reportCard->update(['pdf_status' => 'failed']);
            throw $e;
        }
    }
}
