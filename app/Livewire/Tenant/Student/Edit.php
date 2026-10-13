<?php
namespace App\Livewire\Tenant\Student;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\House;
use App\Domain\Tenant\Models\Student;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Student $student;
    public string $admissionNumber=''; public string $firstName=''; public string $lastName=''; public string $gender='';
    public string $status='active';
    public function mount(Student $student): void {
        $this->authorize('update', $student); $this->student = $student;
        $this->admissionNumber = $student->admission_number; $this->firstName = $student->first_name;
        $this->lastName = (string) $student->last_name; $this->gender = (string) $student->gender; $this->status = $student->status;
    }
    public function save(AuditService $audit): void
    {
        $this->validate([
            'admissionNumber'=>['required','string','max:50', Rule::unique('students','admission_number')->where('school_id',$this->student->school_id)->ignore($this->student->id)],
            'firstName'=>['required','string','max:100'], 'status'=>['required','in:active,inactive,alumni,transferred_out'],
        ]);
        $old = $this->student->only(['admission_number','first_name','status']);
        $this->student->update(['admission_number'=>$this->admissionNumber,'first_name'=>$this->firstName,'last_name'=>$this->lastName ?: null,'gender'=>$this->gender ?: null,'status'=>$this->status]);
        $audit->logSchool(auth()->id(), 'student.updated', Student::class, $this->student->id, $old, ['status'=>$this->status]);
        session()->flash('success', 'Student updated.');
        $this->redirect(route('tenant.students.show', $this->student), navigate: true);
    }
    public function render() { return view('livewire.tenant.student.edit', ['houses'=>House::orderBy('name')->get()]); }
}
