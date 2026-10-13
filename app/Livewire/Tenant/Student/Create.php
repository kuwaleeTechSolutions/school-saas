<?php
namespace App\Livewire\Tenant\Student;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\House;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $admissionNumber=''; public string $firstName=''; public string $middleName=''; public string $lastName='';
    public string $gender=''; public string $dateOfBirth=''; public string $bloodGroup=''; public string $religion=''; public string $category='';
    public string $phone=''; public string $email=''; public string $address=''; public string $city=''; public string $state=''; public string $pincode='';
    public string $admissionDate=''; public ?int $houseId=null; public ?int $sectionId=null;
    public string $allergies=''; public string $medicalNotes=''; public string $emergencyContactName=''; public string $emergencyContactPhone='';
    public string $guardianName=''; public string $guardianPhone=''; public string $guardianRelation='father';

    public function mount(): void { $this->authorize('create', Student::class); $this->admissionDate = now()->format('Y-m-d'); }

    public function save(TenantContext $tenant, EntitlementServiceContract $entitlements, AuditService $audit): void
    {
        $school = $tenant->school();
        $this->validate([
            'admissionNumber' => ['required','string','max:50', Rule::unique('students','admission_number')->where('school_id', $school->id)],
            'firstName' => ['required','string','max:100'], 'lastName' => ['nullable','string','max:100'],
            'gender' => ['nullable','in:male,female,other'], 'dateOfBirth' => ['nullable','date'],
            'sectionId' => ['required','exists:sections,id'], 'admissionDate' => ['required','date'],
            'guardianName' => ['required','string','max:255'], 'guardianPhone' => ['required','string','max:20'],
            'guardianRelation' => ['required','in:father,mother,guardian,other'],
        ]);
        if (! $entitlements->limitAvailable('students', 1)) { $this->addError('admissionNumber', "Your plan's student limit has been reached."); return; }
        $section = Section::with('schoolClass')->findOrFail($this->sectionId);
        $session = $school->activeAcademicSession();
        $student = Student::create([
            'admission_number'=>$this->admissionNumber,'first_name'=>$this->firstName,'middle_name'=>$this->middleName ?: null,'last_name'=>$this->lastName ?: null,
            'gender'=>$this->gender ?: null,'date_of_birth'=>$this->dateOfBirth ?: null,'blood_group'=>$this->bloodGroup ?: null,'religion'=>$this->religion ?: null,
            'category'=>$this->category ?: null,'phone'=>$this->phone ?: null,'email'=>$this->email ?: null,'address'=>$this->address ?: null,
            'city'=>$this->city ?: null,'state'=>$this->state ?: null,'pincode'=>$this->pincode ?: null,'admission_date'=>$this->admissionDate,'house_id'=>$this->houseId,
            'allergies'=>$this->allergies ?: null,'medical_notes'=>$this->medicalNotes ?: null,'emergency_contact_name'=>$this->emergencyContactName ?: null,
            'emergency_contact_phone'=>$this->emergencyContactPhone ?: null,'status'=>'active',
        ]);
        $student->enrollments()->create(['academic_session_id'=>$session->id,'class_id'=>$section->class_id,'section_id'=>$section->id,'status'=>'active']);
        $guardian = Guardian::create(['name'=>$this->guardianName,'phone'=>$this->guardianPhone]);
        $student->guardians()->attach($guardian->id, ['relation'=>$this->guardianRelation,'is_primary_contact'=>true]);
        $audit->logSchool(auth()->id(), 'student.created', Student::class, $student->id, null, ['admission_number'=>$student->admission_number]);
        session()->flash('success', 'Student created successfully.');
        $this->redirect(route('tenant.students.show', $student), navigate: true);
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        $sections = $session ? Section::whereHas('schoolClass', fn($q) => $q->where('academic_session_id', $session->id))->with('schoolClass')->orderBy('class_id')->orderBy('name')->get() : collect();
        return view('livewire.tenant.student.create', ['sections'=>$sections, 'houses'=>House::orderBy('name')->get()]);
    }
}
