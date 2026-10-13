<?php
namespace App\Livewire\Tenant\Examinations;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Exam;
use App\Domain\Tenant\Models\ExamSubject;
use App\Domain\Tenant\Models\ExamType;
use App\Domain\Tenant\Models\SchoolClass;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public ?int $examTypeId = null;
    public ?int $classId = null;
    public string $name = '';
    public string $startsOn = '';
    public string $endsOn = '';
    public array $subjectRows = [];

    public function mount(): void { $this->authorize('create', Exam::class); }

    public function loadSubjectsForClass(): void
    {
        $class = SchoolClass::with('subjects')->find($this->classId);
        $this->subjectRows = $class ? $class->subjects->map(fn($s) => ['subject_id' => $s->id, 'subject_name' => $s->name, 'max_marks' => 100, 'pass_marks' => 35])->all() : [];
    }

    public function save(TenantContext $tenant, AuditService $audit): void
    {
        $this->validate(['examTypeId' => ['required', 'exists:exam_types,id'], 'classId' => ['required', 'exists:classes,id'], 'name' => ['required', 'string', 'max:255'], 'startsOn' => ['nullable', 'date'], 'endsOn' => ['nullable', 'date', 'after_or_equal:startsOn']]);
        $school = $tenant->school();
        $session = $school->activeAcademicSession();
        $exam = Exam::create(['exam_type_id' => $this->examTypeId, 'academic_session_id' => $session->id, 'class_id' => $this->classId, 'name' => $this->name, 'starts_on' => $this->startsOn ?: null, 'ends_on' => $this->endsOn ?: null, 'status' => 'draft']);
        foreach ($this->subjectRows as $row) {
            ExamSubject::create(['exam_id' => $exam->id, 'subject_id' => $row['subject_id'], 'max_marks' => $row['max_marks'], 'pass_marks' => $row['pass_marks'], 'status' => 'pending']);
        }
        $audit->logSchool(auth()->id(), 'exam.created', Exam::class, $exam->id, null, ['name' => $exam->name, 'subject_count' => count($this->subjectRows)]);
        session()->flash('success', 'Exam created.');
        $this->redirect(route('tenant.examinations.index'), navigate: true);
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        return view('livewire.tenant.examinations.create', [
            'examTypes' => ExamType::orderBy('name')->get(),
            'classes' => $session ? SchoolClass::where('academic_session_id', $session->id)->orderBy('sort_order')->get() : collect(),
        ]);
    }
}
