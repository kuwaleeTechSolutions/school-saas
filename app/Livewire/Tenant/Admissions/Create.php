<?php
namespace App\Livewire\Tenant\Admissions;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AdmissionApplication;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $applicantFirstName = '';
    public string $applicantLastName = '';
    public string $applicantDateOfBirth = '';
    public string $applicantGender = '';
    public string $guardianName = '';
    public string $guardianPhone = '';
    public string $guardianEmail = '';
    public string $applyingForClass = '';
    public string $previousSchool = '';
    public float $applicationFeeAmount = 0;

    public function mount(): void { $this->authorize('create', AdmissionApplication::class); }

    public function save(TenantContext $tenant, AuditService $audit): void
    {
        $this->authorize('create', AdmissionApplication::class);
        $this->validate([
            'applicantFirstName' => ['required', 'string', 'max:100'], 'applicantLastName' => ['nullable', 'string', 'max:100'],
            'applicantGender' => ['nullable', 'in:male,female,other'], 'guardianName' => ['required', 'string', 'max:255'],
            'guardianPhone' => ['required', 'string', 'max:20'], 'guardianEmail' => ['nullable', 'email'],
            'applyingForClass' => ['required', 'string', 'max:100'],
        ]);
        $school = $tenant->school();
        $session = $school->activeAcademicSession();
        if (! $session) { $this->addError('applyingForClass', 'No active academic session. Complete onboarding first.'); return; }
        $application = AdmissionApplication::create([
            'academic_session_id' => $session->id, 'applicant_first_name' => $this->applicantFirstName,
            'applicant_last_name' => $this->applicantLastName ?: null, 'applicant_date_of_birth' => $this->applicantDateOfBirth ?: null,
            'applicant_gender' => $this->applicantGender ?: null, 'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone, 'guardian_email' => $this->guardianEmail ?: null,
            'applying_for_class' => $this->applyingForClass, 'previous_school' => $this->previousSchool ?: null,
            'stage' => 'application', 'application_fee_amount' => $this->applicationFeeAmount,
        ]);
        $audit->logSchool(auth()->id(), 'admission.application_created', AdmissionApplication::class, $application->id);
        session()->flash('success', 'Application created.');
        $this->redirect(route('tenant.admissions.show', $application), navigate: true);
    }

    public function render() { return view('livewire.tenant.admissions.create'); }
}
