<?php
namespace App\Livewire\Tenant\Assignments;
use App\Domain\Tenant\Models\Assignment;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Services\AssignmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public ?int $sectionId = null;
    public ?int $subjectId = null;
    public string $title = '';
    public string $instructions = '';
    public float $maxMarks = 100;
    public string $assignedDate;
    public string $dueDate = '';

    public function mount(): void { $this->authorize('create', Assignment::class); $this->assignedDate = now()->format('Y-m-d'); }

    public function save(AssignmentService $assignmentService): void
    {
        $this->validate(['sectionId' => ['required', 'exists:sections,id'], 'subjectId' => ['required', 'exists:subjects,id'], 'title' => ['required', 'string', 'max:255'], 'maxMarks' => ['required', 'numeric', 'min:1'], 'assignedDate' => ['required', 'date'], 'dueDate' => ['required', 'date', 'after_or_equal:assignedDate']]);
        $section = Section::findOrFail($this->sectionId);
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $assignmentService->assign([
            'class_id' => $section->class_id, 'section_id' => $section->id, 'subject_id' => $this->subjectId,
            'teacher_id' => $teacher->id, 'title' => $this->title, 'instructions' => $this->instructions ?: null,
            'max_marks' => $this->maxMarks, 'assigned_date' => $this->assignedDate, 'due_date' => $this->dueDate,
        ], auth()->user());
        session()->flash('success', 'Assignment created.');
        $this->redirect(route('tenant.assignments.index'), navigate: true);
    }

    public function render()
    {
        $section = $this->sectionId ? Section::with('schoolClass.subjects')->find($this->sectionId) : null;
        return view('livewire.tenant.assignments.create', ['sections' => Section::with('schoolClass')->orderBy('class_id')->orderBy('name')->get(), 'subjects' => $section?->schoolClass->subjects ?? collect()]);
    }
}
