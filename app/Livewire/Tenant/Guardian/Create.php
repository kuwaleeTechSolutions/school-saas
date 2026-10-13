<?php
namespace App\Livewire\Tenant\Guardian;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $occupation = '';
    public string $address = '';
    public array $linkedStudentIds = [];
    public string $relation = 'father';

    public function mount(): void { $this->authorize('create', Guardian::class); }

    public function save(AuditService $audit): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255'], 'phone' => ['required', 'string', 'max:20'], 'email' => ['nullable', 'email'], 'relation' => ['required', 'in:father,mother,guardian,other']]);
        $guardian = Guardian::create(['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email ?: null, 'occupation' => $this->occupation ?: null, 'address' => $this->address ?: null]);
        foreach ($this->linkedStudentIds as $studentId) {
            $guardian->students()->attach($studentId, ['relation' => $this->relation, 'is_primary_contact' => false]);
        }
        $audit->logSchool(auth()->id(), 'guardian.created', Guardian::class, $guardian->id);
        session()->flash('success', 'Guardian added successfully.');
        $this->redirect(route('tenant.guardians.index'), navigate: true);
    }

    public function render() { return view('livewire.tenant.guardian.create', ['students' => Student::orderBy('first_name')->get()]); }
}
