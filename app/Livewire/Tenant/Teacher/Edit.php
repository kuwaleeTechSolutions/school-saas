<?php
namespace App\Livewire\Tenant\Teacher;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Teacher;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Teacher $teacher;
    public string $qualification = '';
    public string $specialization = '';
    public string $employeeCode = '';
    public string $status = 'active';

    public function mount(Teacher $teacher): void
    {
        $this->authorize('update', $teacher);
        $this->teacher = $teacher;
        $this->qualification = (string) $teacher->qualification;
        $this->specialization = (string) $teacher->specialization;
        $this->employeeCode = (string) $teacher->employee_code;
        $this->status = $teacher->status;
    }

    public function save(AuditService $audit): void
    {
        $this->validate(['status' => ['required', 'in:active,on_leave,inactive']]);
        $old = $this->teacher->only(['status']);
        $this->teacher->update(['qualification' => $this->qualification ?: null, 'specialization' => $this->specialization ?: null, 'employee_code' => $this->employeeCode ?: null, 'status' => $this->status]);
        $audit->logSchool(auth()->id(), 'teacher.updated', Teacher::class, $this->teacher->id, $old, ['status' => $this->status]);
        session()->flash('success', 'Teacher updated.');
        $this->redirect(route('tenant.teachers.index'), navigate: true);
    }

    public function render() { return view('livewire.tenant.teacher.edit'); }
}
