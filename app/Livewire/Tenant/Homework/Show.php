<?php
namespace App\Livewire\Tenant\Homework;
use App\Domain\Tenant\Models\Homework;
use App\Domain\Tenant\Models\HomeworkSubmission;
use App\Domain\Tenant\Services\HomeworkService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Homework $homework;
    public ?int $gradingSubmissionId = null;
    public string $gradePoints = '';
    public string $teacherComment = '';

    public function mount(Homework $homework): void { $this->authorize('update', $homework); $this->homework = $homework; }

    public function startGrading(int $submissionId): void
    {
        $submission = HomeworkSubmission::findOrFail($submissionId);
        $this->authorize('grade', $this->homework);
        $this->gradingSubmissionId = $submissionId;
        $this->gradePoints = (string) $submission->grade_points;
        $this->teacherComment = (string) $submission->teacher_comment;
    }

    public function saveGrade(HomeworkService $homeworkService): void
    {
        $this->authorize('grade', $this->homework);
        $this->validate(['gradePoints' => ['required', 'numeric', 'min:0']]);
        $submission = HomeworkSubmission::findOrFail($this->gradingSubmissionId);
        $homeworkService->grade($submission, (float) $this->gradePoints, $this->teacherComment ?: null, auth()->user());
        $this->reset(['gradingSubmissionId', 'gradePoints', 'teacherComment']);
    }

    public function render() { $this->homework->load(['submissions.student']); return view('livewire.tenant.homework.show'); }
}
