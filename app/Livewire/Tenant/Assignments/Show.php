<?php
namespace App\Livewire\Tenant\Assignments;
use App\Domain\Tenant\Models\Assignment;
use App\Domain\Tenant\Models\AssignmentSubmission;
use App\Domain\Tenant\Services\AssignmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Assignment $assignment;
    public ?int $gradingSubmissionId = null;
    public string $marksObtained = '';
    public string $teacherComment = '';

    public function mount(Assignment $assignment): void { $this->authorize('update', $assignment); $this->assignment = $assignment; }

    public function startGrading(int $submissionId): void
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);
        $this->authorize('grade', $this->assignment);
        $this->gradingSubmissionId = $submissionId;
        $this->marksObtained = (string) $submission->marks_obtained;
        $this->teacherComment = (string) $submission->teacher_comment;
    }

    public function saveGrade(AssignmentService $assignmentService): void
    {
        $this->authorize('grade', $this->assignment);
        $this->validate(['marksObtained' => ['required', 'numeric', 'min:0', 'max:' . $this->assignment->max_marks]]);
        $submission = AssignmentSubmission::findOrFail($this->gradingSubmissionId);
        $assignmentService->grade($submission, (float) $this->marksObtained, $this->teacherComment ?: null, auth()->user());
        $this->reset(['gradingSubmissionId', 'marksObtained', 'teacherComment']);
    }

    public function render() { $this->assignment->load(['submissions.student']); return view('livewire.tenant.assignments.show'); }
}
