<?php
namespace App\Livewire\Tenant\AcademicSessions;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function mount(): void { $this->authorize('viewAny', AcademicSession::class); }

    public function makeCurrent(int $sessionId, AuditService $audit): void
    {
        $school = app(TenantContext::class)->school();
        $session = AcademicSession::where('school_id', $school->id)->findOrFail($sessionId);
        $this->authorize('update', $session);
        $old = $school->activeAcademicSession()?->name;
        $session->markAsCurrent();
        $audit->logSchool(auth()->id(), 'academic_session.activated', AcademicSession::class, $session->id, ['previous_current' => $old], ['current' => $session->name]);
        session()->flash('success', "'{$session->name}' is now the active session.");
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        return view('livewire.tenant.academic-sessions.index', [
            'sessions' => $school->academicSessions()->withCount(['classes', 'enrollments'])->orderByDesc('starts_on')->get(),
        ]);
    }
}
