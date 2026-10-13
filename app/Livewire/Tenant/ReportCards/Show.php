<?php
namespace App\Livewire\Tenant\ReportCards;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\ReportCard;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public ReportCard $reportCard;
    public string $teacherRemarks = '';
    public string $principalRemarks = '';

    public function mount(ReportCard $reportCard): void
    {
        $this->authorize('view', $reportCard);
        $this->reportCard = $reportCard;
        $this->teacherRemarks = (string) $reportCard->teacher_remarks;
        $this->principalRemarks = (string) $reportCard->principal_remarks;
    }

    public function saveRemarks(AuditService $audit): void
    {
        $this->authorize('generate', ReportCard::class);
        $this->reportCard->update(['teacher_remarks' => $this->teacherRemarks ?: null, 'principal_remarks' => $this->principalRemarks ?: null]);
        $audit->logSchool(auth()->id(), 'report_card.remarks_updated', ReportCard::class, $this->reportCard->id);
        session()->flash('success', 'Remarks saved. Regenerate the PDF to include them in the printed copy.');
    }

    public function render() { return view('livewire.tenant.report-cards.show'); }
}
