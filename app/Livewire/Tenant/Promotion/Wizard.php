<?php
namespace App\Livewire\Tenant\Promotion;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Domain\Tenant\Services\PromotionService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Wizard extends Component
{
    public int $step = 1;
    public ?int $fromSectionId = null;
    public ?int $toAcademicSessionId = null;
    public array $rows = [];
    public ?string $lastBatchUlid = null;

    public function mount(): void { $this->authorize('viewAny', \App\Domain\Tenant\Models\Student::class); }

    public function loadStudents(): void
    {
        $this->validate(['fromSectionId' => ['required', 'exists:sections,id'], 'toAcademicSessionId' => ['required', 'exists:academic_sessions,id']]);
        $enrollments = StudentEnrollment::where('section_id', $this->fromSectionId)->where('status', 'active')->with('student')->get();
        $this->rows = $enrollments->map(fn($enrollment) => [
            'student_id' => $enrollment->student_id, 'name' => $enrollment->student->fullName(),
            'decision' => 'promoted', 'to_class_id' => null, 'to_section_id' => null,
        ])->all();
        $this->step = 2;
    }

    public function sectionsForTargetClass(int $index): \Illuminate\Support\Collection
    {
        $classId = $this->rows[$index]['to_class_id'] ?? null;
        if (! $classId) return collect();
        return Section::where('class_id', $classId)->orderBy('name')->get();
    }

    public function goToPreview(): void
    {
        foreach ($this->rows as $i => $row) {
            if (in_array($row['decision'], ['promoted', 'retained'], true) && (! $row['to_class_id'] || ! $row['to_section_id'])) {
                $this->addError("rows.{$i}.to_class_id", 'Target class/section required for this decision.');
                return;
            }
        }
        $this->step = 3;
    }

    public function confirm(PromotionService $promotionService, EntitlementServiceContract $entitlements): void
    {
        if (! $entitlements->can('students.edit')) {
            $this->addError('confirm', 'You do not have permission to promote students.');
            return;
        }
        $fromSection = Section::with('schoolClass.academicSession')->findOrFail($this->fromSectionId);
        $fromSession = $fromSection->schoolClass->academicSession;
        $toSession = AcademicSession::findOrFail($this->toAcademicSessionId);
        try {
            $this->lastBatchUlid = $promotionService->promoteBatch($fromSession, $toSession, $this->rows, auth()->user());
            $this->step = 4;
        } catch (RuntimeException $e) {
            $this->addError('confirm', $e->getMessage());
        }
    }

    public function restart(): void { $this->reset(['step', 'fromSectionId', 'toAcademicSessionId', 'rows', 'lastBatchUlid']); $this->step = 1; }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        return view('livewire.tenant.promotion.wizard', [
            'sections' => Section::whereHas('schoolClass', fn($q) => $q->where('school_id', $school->id))->with('schoolClass')->orderBy('class_id')->orderBy('name')->get(),
            'targetSessions' => $school->academicSessions()->orderByDesc('starts_on')->get(),
            'targetClasses' => SchoolClass::where('academic_session_id', $this->toAcademicSessionId)->orderBy('sort_order')->get(),
        ]);
    }
}
