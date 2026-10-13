<?php
namespace App\Livewire\Tenant\Homework;
use App\Domain\Tenant\Models\Homework;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Services\HomeworkService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public ?int $sectionId = null;
    public ?int $subjectId = null;
    public string $title = '';
    public string $instructions = '';
    public string $assignedDate;
    public string $dueDate = '';

    public function mount(): void { $this->authorize('create', Homework::class); $this->assignedDate = now()->format('Y-m-d'); }

    public function save(HomeworkService $homeworkService): void
    {
        $this->validate(['sectionId' => ['required', 'exists:sections,id'], 'subjectId' => ['required', 'exists:subjects,id'], 'title' => ['required', 'string', 'max:255'], 'assignedDate' => ['required', 'date'], 'dueDate' => ['required', 'date', 'after_or_equal:assignedDate']]);
        $section = Section::findOrFail($this->sectionId);
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $homeworkService->assign([
            'class_id' => $section->class_id, 'section_id' => $section->id, 'subject_id' => $this->subjectId,
            'teacher_id' => $teacher->id, 'title' => $this->title, 'instructions' => $this->instructions ?: null,
            'assigned_date' => $this->assignedDate, 'due_date' => $this->dueDate,
        ], auth()->user());
        session()->flash('success', 'Homework assigned.');
        $this->redirect(route('tenant.homework.index'), navigate: true);
    }

    public function render()
    {
        $section = $this->sectionId ? Section::with('schoolClass.subjects')->find($this->sectionId) : null;
        return view('livewire.tenant.homework.create', ['sections' => Section::with('schoolClass')->orderBy('class_id')->orderBy('name')->get(), 'subjects' => $section?->schoolClass->subjects ?? collect()]);
    }
}
