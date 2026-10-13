<?php
namespace App\Livewire\Tenant\Results;
use App\Domain\Tenant\Models\Exam;
use App\Domain\Tenant\Models\ExamSubject;
use App\Domain\Tenant\Models\Mark;
use App\Domain\Tenant\Services\MarksWorkflowService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class MarksEntry extends Component
{
    public Exam $exam;
    public ?int $examSubjectId = null;
    public array $entries = [];

    public function mount(Exam $exam): void { $this->authorize('view', $exam); $this->exam = $exam; }

    public function loadSubject(MarksWorkflowService $workflow): void
    {
        $this->validate(['examSubjectId' => ['required', 'exists:exam_subjects,id']]);
        $examSubject = ExamSubject::findOrFail($this->examSubjectId);
        $this->authorize('enterMarks', $examSubject);
        $workflow->ensureMarkRowsExist($examSubject);
        $marks = Mark::where('exam_subject_id', $examSubject->id)->with('student')->get();
        $this->entries = $marks->map(fn(Mark $m) => ['student_id' => $m->student_id, 'student_name' => $m->student->fullName(), 'marks_obtained' => $m->marks_obtained, 'is_absent' => $m->is_absent])->values()->all();
    }

    public function saveEntries(MarksWorkflowService $workflow): void
    {
        $examSubject = ExamSubject::findOrFail($this->examSubjectId);
        $this->authorize('enterMarks', $examSubject);
        foreach ($this->entries as $i => $entry) {
            if (! $entry['is_absent'] && $entry['marks_obtained'] !== null && (float) $entry['marks_obtained'] > (float) $examSubject->max_marks) {
                $this->addError("entries.{$i}.marks_obtained", "Cannot exceed max marks ({$examSubject->max_marks}).");
                return;
            }
        }
        try {
            $workflow->enterMarks($examSubject, $this->entries, auth()->user());
            session()->flash('success', 'Marks saved.');
        } catch (RuntimeException $e) {
            $this->addError('entries', $e->getMessage());
        }
    }

    public function submitForApproval(MarksWorkflowService $workflow): void
    {
        $examSubject = ExamSubject::findOrFail($this->examSubjectId);
        $this->authorize('enterMarks', $examSubject);
        try {
            $workflow->transition($examSubject, 'submitted', auth()->user());
            session()->flash('success', 'Submitted for approval.');
            $this->loadSubject($workflow);
        } catch (RuntimeException $e) {
            $this->addError('entries', $e->getMessage());
        }
    }

    public function render() { $this->exam->load('examSubjects.subject'); return view('livewire.tenant.results.marks-entry'); }
}
