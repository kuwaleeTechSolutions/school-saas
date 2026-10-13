<?php

namespace App\Livewire\Tenant\Onboarding;

use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\SchoolOnboardingProgress;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Wizard extends Component
{
    use WithFileUploads;

    public const STEPS = [
        1 => 'school_info', 2 => 'branding', 3 => 'academic_session', 4 => 'classes',
        5 => 'sections', 6 => 'subjects', 7 => 'admin_users', 8 => 'package_confirmation',
        9 => 'import_students', 10 => 'complete_setup',
    ];

    public int $currentStep = 1;
    public string $schoolName = '';
    public string $schoolEmail = '';
    public string $schoolPhone = '';
    public string $schoolAddress = '';
    public string $board = '';
    public string $affiliationNumber = '';
    public $logo;
    public string $sessionName = '';
    public string $sessionStartsOn = '';
    public string $sessionEndsOn = '';
    public string $classNamesBulk = "Grade 1\nGrade 2\nGrade 3";
    public string $sectionNamesBulk = "A\nB";
    public string $subjectNamesBulk = "English\nMathematics\nScience";
    public string $newAdminName = '';
    public string $newAdminEmail = '';
    public $studentsCsv;
    public array $importPreview = [];
    public array $importErrors = [];

    public function mount(): void
    {
        $school = app(TenantContext::class)->school();
        $this->schoolName = $school->name;
        $this->schoolEmail = $school->email;
        $this->schoolPhone = (string) $school->phone;
        $this->schoolAddress = (string) $school->address;
        $this->board = (string) $school->board;
        $this->affiliationNumber = (string) $school->affiliation_number;
        $this->currentStep = $this->firstIncompleteStep();
    }

    protected function firstIncompleteStep(): int
    {
        $school = app(TenantContext::class)->school();
        $completed = $school->onboardingProgress()->where('is_completed', true)->orWhere('is_skipped', true)->pluck('step_code')->all();
        foreach (self::STEPS as $step => $code) {
            if (! in_array($code, $completed, true)) return $step;
        }
        return 10;
    }

    protected function markStepDone(int $step, bool $skipped = false): void
    {
        $school = app(TenantContext::class)->school();
        SchoolOnboardingProgress::updateOrCreate(
            ['school_id' => $school->id, 'step_code' => self::STEPS[$step]],
            ['is_completed' => ! $skipped, 'is_skipped' => $skipped, 'completed_at' => now()]
        );
    }

    public function goTo(int $step): void { $this->currentStep = max(1, min(10, $step)); }

    public function skipStep(AuditService $audit): void
    {
        if (! in_array($this->currentStep, [2, 9], true)) return;
        $this->markStepDone($this->currentStep, skipped: true);
        $audit->logSchool(auth()->id(), 'onboarding.step_skipped', null, null, null, ['step' => self::STEPS[$this->currentStep]]);
        $this->goTo($this->currentStep + 1);
    }

    public function saveSchoolInfo(AuditService $audit): void
    {
        $this->validate([
            'schoolName' => ['required', 'string', 'max:255'], 'schoolEmail' => ['required', 'email'],
            'schoolPhone' => ['nullable', 'string', 'max:20'], 'schoolAddress' => ['nullable', 'string', 'max:500'],
            'board' => ['nullable', 'string', 'max:100'], 'affiliationNumber' => ['nullable', 'string', 'max:100'],
        ]);
        $school = app(TenantContext::class)->school();
        $old = $school->only(['name', 'email', 'phone', 'address', 'board', 'affiliation_number']);
        $school->update(['name' => $this->schoolName, 'email' => $this->schoolEmail, 'phone' => $this->schoolPhone, 'address' => $this->schoolAddress, 'board' => $this->board, 'affiliation_number' => $this->affiliationNumber]);
        $audit->logSchool(auth()->id(), 'school.updated', School::class, $school->id, $old, $school->only(['name', 'email', 'phone', 'address', 'board', 'affiliation_number']));
        $this->markStepDone(1);
        $this->goTo(2);
    }

    public function saveBranding(): void
    {
        $this->validate(['logo' => ['nullable', 'image', 'max:2048']]);
        if ($this->logo) {
            $school = app(TenantContext::class)->school();
            $path = $this->logo->store("schools/{$school->id}/branding", 'local');
            $school->update(['logo' => $path]);
        }
        $this->markStepDone(2);
        $this->goTo(3);
    }

    public function saveAcademicSession(): void
    {
        $this->validate(['sessionName' => ['required', 'string', 'max:50'], 'sessionStartsOn' => ['required', 'date'], 'sessionEndsOn' => ['required', 'date', 'after:sessionStartsOn']]);
        $school = app(TenantContext::class)->school();
        $session = AcademicSession::create(['school_id' => $school->id, 'name' => $this->sessionName, 'starts_on' => $this->sessionStartsOn, 'ends_on' => $this->sessionEndsOn]);
        $session->markAsCurrent();
        $this->markStepDone(3);
        $this->goTo(4);
    }

    public function saveClasses(): void
    {
        $this->validate(['classNamesBulk' => ['required', 'string']]);
        $school = app(TenantContext::class)->school();
        $session = $school->fresh()->activeAcademicSession();
        $names = $this->splitLines($this->classNamesBulk);
        if (! $this->guardAgainstLimit('classrooms', count($names))) return;
        foreach ($names as $i => $name) {
            SchoolClass::updateOrCreate(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => $name], ['sort_order' => $i]);
        }
        $this->markStepDone(4);
        $this->goTo(5);
    }

    public function saveSections(): void
    {
        $this->validate(['sectionNamesBulk' => ['required', 'string']]);
        $school = app(TenantContext::class)->school();
        $session = $school->fresh()->activeAcademicSession();
        $sectionNames = $this->splitLines($this->sectionNamesBulk);
        foreach ($session->classes as $class) {
            foreach ($sectionNames as $i => $name) {
                Section::updateOrCreate(['school_id' => $school->id, 'class_id' => $class->id, 'name' => $name], ['sort_order' => $i]);
            }
        }
        $this->markStepDone(5);
        $this->goTo(6);
    }

    public function saveSubjects(): void
    {
        $this->validate(['subjectNamesBulk' => ['required', 'string']]);
        $school = app(TenantContext::class)->school();
        $session = $school->fresh()->activeAcademicSession();
        $subjectNames = $this->splitLines($this->subjectNamesBulk);
        $subjectIds = [];
        foreach ($subjectNames as $name) {
            $subject = Subject::updateOrCreate(['school_id' => $school->id, 'name' => $name]);
            $subjectIds[] = $subject->id;
        }
        foreach ($session->classes as $class) $class->subjects()->syncWithoutDetaching($subjectIds);
        $this->markStepDone(6);
        $this->goTo(7);
    }

    public function addAdminUser(AuditService $audit): void
    {
        $this->validate(['newAdminName' => ['required', 'string', 'max:255'], 'newAdminEmail' => ['required', 'email', 'unique:users,email']]);
        if (! $this->guardAgainstLimit('admins', 1)) return;
        $user = User::create(['name' => $this->newAdminName, 'email' => $this->newAdminEmail, 'password' => Hash::make(Str::random(16)), 'status' => 'active']);
        $adminRole = Role::where('name', 'Administrator')->whereNull('school_id')->first();
        if ($adminRole) $user->assignRole($adminRole);
        $audit->logSchool(auth()->id(), 'user.created', User::class, $user->id, null, ['email' => $user->email, 'role' => 'Administrator']);
        $this->newAdminName = '';
        $this->newAdminEmail = '';
        $this->dispatch('admin-added');
    }

    public function finishAdminUsersStep(): void { $this->markStepDone(7); $this->goTo(8); }
    public function confirmPackage(): void { $this->markStepDone(8); $this->goTo(9); }

    public function previewImport(): void
    {
        $this->validate(['studentsCsv' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $rows = array_map('str_getcsv', file($this->studentsCsv->getRealPath()));
        $header = array_map('trim', array_shift($rows));
        $required = ['admission_number', 'first_name', 'last_name', 'class', 'section'];
        $missing = array_diff($required, $header);
        if (! empty($missing)) { $this->addError('studentsCsv', 'CSV is missing required columns: ' . implode(', ', $missing)); return; }
        $this->importPreview = [];
        $this->importErrors = [];
        foreach ($rows as $i => $row) {
            if (count($row) !== count($header)) { $this->importErrors[] = "Row " . ($i + 2) . ': column count mismatch.'; continue; }
            $this->importPreview[] = array_combine($header, $row);
        }
    }

    public function confirmImport(AuditService $audit): void
    {
        $school = app(TenantContext::class)->school();
        $session = $school->fresh()->activeAcademicSession();
        if (! $this->guardAgainstLimit('students', count($this->importPreview))) return;
        $created = 0;
        foreach ($this->importPreview as $row) {
            $class = SchoolClass::where('school_id', $school->id)->where('academic_session_id', $session->id)->where('name', $row['class'])->first();
            $section = $class ? Section::where('class_id', $class->id)->where('name', $row['section'])->first() : null;
            if (! $class || ! $section) { $this->importErrors[] = "Skipped {$row['admission_number']}: class/section not found."; continue; }
            $student = Student::create(['school_id' => $school->id, 'admission_number' => $row['admission_number'], 'first_name' => $row['first_name'], 'last_name' => $row['last_name'], 'admission_date' => now()]);
            $student->enrollments()->create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'class_id' => $class->id, 'section_id' => $section->id, 'status' => 'active']);
            $created++;
        }
        $audit->logSchool(auth()->id(), 'students.bulk_imported', Student::class, null, null, ['created' => $created]);
        $this->markStepDone(9);
        $this->goTo(10);
    }

    public function completeSetup(AuditService $audit): void
    {
        $school = app(TenantContext::class)->school();
        $school->update(['onboarding_status' => 'completed']);
        $this->markStepDone(10);
        $audit->logSchool(auth()->id(), 'onboarding.completed', School::class, $school->id);
        $this->redirect(route('tenant.dashboard'), navigate: true);
    }

    protected function splitLines(string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $raw))->map(fn ($l) => trim($l))->filter()->unique()->values()->all();
    }

    protected function guardAgainstLimit(string $metric, int $requestedCount): bool
    {
        $entitlements = app(\App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract::class);
        if (! $entitlements->limitAvailable($metric, $requestedCount)) {
            $remaining = $entitlements->remaining($metric);
            $this->addError('limit', "This would exceed your plan's {$metric} limit (remaining: {$remaining}). Please upgrade your plan.");
            return false;
        }
        return true;
    }

    public function render() { return view('livewire.tenant.onboarding.wizard'); }
}
