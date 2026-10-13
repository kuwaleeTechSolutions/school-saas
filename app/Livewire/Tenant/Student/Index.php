<?php
namespace App\Livewire\Tenant\Student;
use App\Domain\Tenant\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = ''; public string $statusFilter = '';
    public function mount(): void { $this->authorize('viewAny', Student::class); }
    public function render()
    {
        $students = Student::query()->with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])
            ->when($this->search, fn($q) => $q->where(fn($q2) => $q2->where('first_name','like',"%{$this->search}%")->orWhere('last_name','like',"%{$this->search}%")->orWhere('admission_number','like',"%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('first_name')->paginate(15);
        return view('livewire.tenant.student.index', ['students' => $students]);
    }
}
