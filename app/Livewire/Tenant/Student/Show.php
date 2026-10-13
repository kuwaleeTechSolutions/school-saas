<?php
namespace App\Livewire\Tenant\Student;
use App\Domain\Tenant\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Student $student;
    public function mount(Student $student): void { $this->authorize('view', $student); $this->student = $student; }
    public function render()
    {
        $this->student->load(['guardians','documents','house','currentEnrollment.schoolClass','currentEnrollment.section']);
        return view('livewire.tenant.student.show');
    }
}
