<?php
namespace App\Livewire\Tenant\AcademicSessions;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $name = '';
    public string $startsOn = '';
    public string $endsOn = '';
    public bool $makeCurrentNow = false;

    public function mount(): void { $this->authorize('create', AcademicSession::class); }

    public function save(TenantContext $tenant, AuditService $audit): void
    {
        $this->authorize('create', AcademicSession::class);
        $school = $tenant->school();
        $this->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('academic_sessions', 'name')->where('school_id', $school->id)],
            'startsOn' => ['required', 'date'], 'endsOn' => ['required', 'date', 'after:startsOn'],
        ]);
        $session = AcademicSession::create(['school_id' => $school->id, 'name' => $this->name, 'starts_on' => $this->startsOn, 'ends_on' => $this->endsOn]);
        if ($this->makeCurrentNow) $session->markAsCurrent();
        $audit->logSchool(auth()->id(), 'academic_session.created', AcademicSession::class, $session->id);
        session()->flash('success', "Session '{$session->name}' created.");
        $this->redirect(route('tenant.academic-sessions.index'), navigate: true);
    }

    public function render() { return view('livewire.tenant.academic-sessions.create'); }
}
