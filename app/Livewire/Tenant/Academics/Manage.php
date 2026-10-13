<?php
namespace App\Livewire\Tenant\Academics;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Subject;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Manage extends Component
{
    public string $tab = 'classes';
    public string $newClassName = '';
    public ?int $sectionClassId = null;
    public string $newSectionName = '';
    public ?int $newSectionCapacity = null;
    public string $newSubjectName = '';
    public string $newSubjectCode = '';

    public function mount(): void { $this->authorize('viewAny', SchoolClass::class); }

    public function addClass(EntitlementServiceContract $entitlements, AuditService $audit): void
    {
        $this->authorize('create', SchoolClass::class);
        $this->validate(['newClassName' => ['required', 'string', 'max:100']]);
        if (! $entitlements->limitAvailable('classrooms', 1)) {
            $this->addError('newClassName', "Your plan's classroom limit has been reached.");
            return;
        }
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        if (! $session) { $this->addError('newClassName', 'Create an academic session first (see Onboarding).'); return; }
        $class = SchoolClass::create(['academic_session_id' => $session->id, 'name' => $this->newClassName]);
        $audit->logSchool(auth()->id(), 'class.created', SchoolClass::class, $class->id);
        $this->newClassName = '';
    }

    public function deleteClass(int $classId, AuditService $audit): void
    {
        $class = SchoolClass::findOrFail($classId);
        $this->authorize('delete', $class);
        $class->delete();
        $audit->logSchool(auth()->id(), 'class.deleted', SchoolClass::class, $classId);
    }

    public function addSection(AuditService $audit): void
    {
        $this->authorize('create', SchoolClass::class);
        $this->validate(['sectionClassId' => ['required', 'exists:classes,id'], 'newSectionName' => ['required', 'string', 'max:50'], 'newSectionCapacity' => ['nullable', 'integer', 'min:1']]);
        $section = Section::create(['class_id' => $this->sectionClassId, 'name' => $this->newSectionName, 'capacity' => $this->newSectionCapacity]);
        $audit->logSchool(auth()->id(), 'section.created', Section::class, $section->id);
        $this->newSectionName = '';
        $this->newSectionCapacity = null;
    }

    public function deleteSection(int $sectionId, AuditService $audit): void
    {
        $section = Section::findOrFail($sectionId);
        $this->authorize('delete', $section->schoolClass);
        $section->delete();
        $audit->logSchool(auth()->id(), 'section.deleted', Section::class, $sectionId);
    }

    public function addSubject(AuditService $audit): void
    {
        $this->authorize('create', Subject::class);
        $this->validate(['newSubjectName' => ['required', 'string', 'max:100'], 'newSubjectCode' => ['nullable', 'string', 'max:20']]);
        $subject = Subject::create(['name' => $this->newSubjectName, 'code' => $this->newSubjectCode ?: null]);
        $audit->logSchool(auth()->id(), 'subject.created', Subject::class, $subject->id);
        $this->newSubjectName = '';
        $this->newSubjectCode = '';
    }

    public function deleteSubject(int $subjectId, AuditService $audit): void
    {
        $subject = Subject::findOrFail($subjectId);
        $this->authorize('delete', $subject);
        $subject->delete();
        $audit->logSchool(auth()->id(), 'subject.deleted', Subject::class, $subjectId);
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        return view('livewire.tenant.academics.manage', [
            'classes' => $session ? SchoolClass::where('academic_session_id', $session->id)->withCount('sections')->orderBy('sort_order')->get() : collect(),
            'sections' => $session ? Section::whereHas('schoolClass', fn($q) => $q->where('academic_session_id', $session->id))->with('schoolClass')->get() : collect(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }
}
