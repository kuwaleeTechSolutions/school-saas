<?php
namespace App\Livewire\Tenant\Transport\Assignments;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentTransportAssignment;
use App\Domain\Tenant\Models\TransportRoute;
use App\Domain\Tenant\Services\TransportService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?int $studentId = null;
    public ?int $routeId = null;
    public ?int $stopId = null;

    public function mount(): void { $this->authorize('viewAny', TransportRoute::class); }

    public function assign(TransportService $transportService): void
    {
        $this->authorize('create', TransportRoute::class);
        $this->validate(['studentId' => ['required', 'exists:students,id'], 'routeId' => ['required', 'exists:transport_routes,id']]);
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        try {
            $student = Student::findOrFail($this->studentId);
            $route = TransportRoute::findOrFail($this->routeId);
            $transportService->assignStudent($student, $route, $session->id, $this->stopId, auth()->user());
            $this->reset(['studentId', 'routeId', 'stopId']);
            session()->flash('success', 'Student assigned to route. Their fee structure will now reflect any transport-dependent charges next time invoices are generated.');
        } catch (RuntimeException $e) {
            $this->addError('routeId', $e->getMessage());
        }
    }

    public function unassign(int $studentId, TransportService $transportService): void
    {
        $this->authorize('create', TransportRoute::class);
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        $student = Student::findOrFail($studentId);
        $transportService->unassignStudent($student, $session->id, auth()->user());
        session()->flash('success', 'Student unassigned from transport.');
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        return view('livewire.tenant.transport.assignments.index', [
            'students' => Student::where('status', 'active')->orderBy('first_name')->get(),
            'routes' => TransportRoute::where('is_active', true)->with('stops')->orderBy('name')->get(),
            'assignments' => $session ? StudentTransportAssignment::where('academic_session_id', $session->id)->with(['student', 'route', 'stop'])->get() : collect(),
        ]);
    }
}
