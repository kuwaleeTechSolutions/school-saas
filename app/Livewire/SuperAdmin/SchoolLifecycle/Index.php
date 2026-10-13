<?php
namespace App\Livewire\SuperAdmin\SchoolLifecycle;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\School;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.super-admin')]
class Index extends Component
{
    use WithPagination;
    public string $search = ''; public ?string $statusFilter = null;
    public function render()
    {
        $schools = School::query()->with('activeSubscription.plan')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')->paginate(10);
        return view('livewire.super-admin.school-lifecycle.index', ['schools' => $schools]);
    }
    public function suspend(int $schoolId, AuditService $audit): void
    {
        $school = School::findOrFail($schoolId); $old = $school->status;
        $school->update(['status' => 'suspended']);
        $audit->logPlatform(Auth::guard('admin')->id(), 'school.suspended', School::class, $school->id, ['status'=>$old], ['status'=>'suspended']);
    }
    public function activate(int $schoolId, AuditService $audit): void
    {
        $school = School::findOrFail($schoolId); $old = $school->status;
        $school->update(['status' => 'active']);
        $audit->logPlatform(Auth::guard('admin')->id(), 'school.activated', School::class, $school->id, ['status'=>$old], ['status'=>'active']);
    }
}
