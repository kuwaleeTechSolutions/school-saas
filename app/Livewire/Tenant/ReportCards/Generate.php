<?php
namespace App\Livewire\Tenant\ReportCards;
use App\Domain\Tenant\Models\Exam;
use App\Domain\Tenant\Models\ReportCard;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Domain\Tenant\Services\ReportCardService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Generate extends Component
{
    public ?int $examId = null;
    public function mount(): void { $this->authorize('generate', ReportCard::class); }

    public function generateForClass(ReportCardService $reportCardService): void
    {
        $this->validate(['examId' => ['required', 'exists:exams,id']]);
        $exam = Exam::findOrFail($this->examId);
        if (! $exam->isFullyPublished()) {
            $this->addError('examId', 'All subjects in this exam must be published before report cards can be generated.');
            return;
        }
        $studentIds = StudentEnrollment::where('class_id', $exam->class_id)->where('academic_session_id', $exam->academic_session_id)->where('status', 'active')->pluck('student_id')->all();
        try {
            $reportCardService->queueBulkGeneration($exam, $studentIds, auth()->user());
            session()->flash('success', count($studentIds) . ' report cards queued for generation.');
        } catch (RuntimeException $e) {
            $this->addError('examId', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant.report-cards.generate', [
            'exams' => Exam::with('schoolClass')->orderByDesc('starts_on')->get(),
            'reportCards' => $this->examId ? ReportCard::where('exam_id', $this->examId)->with('student')->get() : collect(),
        ]);
    }
}
