<?php
namespace App\Livewire\Tenant\Hr\Leave;
use App\Domain\Tenant\Models\LeaveRequest;
use App\Domain\Tenant\Models\StaffProfile;
use App\Domain\Tenant\Services\LeaveService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?int $staffProfileId = null;
    public string $startsOn = '';
    public string $endsOn = '';
    public string $reason = '';
    public ?int $rejectingLeaveId = null;
    public string $rejectionReason = '';

    public function mount(): void { $this->authorize('viewAny', LeaveRequest::class); $this->startsOn = now()->format('Y-m-d'); $this->endsOn = now()->format('Y-m-d'); }

    public function requestLeave(LeaveService $leaveService): void
    {
        $this->authorize('create', LeaveRequest::class);
        $this->validate(['staffProfileId' => ['required', 'exists:staff_profiles,id'], 'startsOn' => ['required', 'date'], 'endsOn' => ['required', 'date'], 'reason' => ['required', 'string', 'max:500']]);
        try {
            $staff = StaffProfile::findOrFail($this->staffProfileId);
            $leaveService->request($staff, Carbon::parse($this->startsOn), Carbon::parse($this->endsOn), $this->reason, auth()->user());
            $this->reset(['startsOn', 'endsOn', 'reason']);
            session()->flash('success', 'Leave requested.');
        } catch (RuntimeException $e) {
            $this->addError('endsOn', $e->getMessage());
        }
    }

    public function approve(int $leaveId, LeaveService $leaveService): void
    {
        $leave = LeaveRequest::findOrFail($leaveId);
        $this->authorize('approve', $leave);
        $leaveService->approve($leave, auth()->user());
        session()->flash('success', 'Leave approved.');
    }

    public function startReject(int $leaveId): void
    {
        $leave = LeaveRequest::findOrFail($leaveId);
        $this->authorize('approve', $leave);
        $this->rejectingLeaveId = $leaveId;
    }

    public function confirmReject(LeaveService $leaveService): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'max:500']]);
        $leave = LeaveRequest::findOrFail($this->rejectingLeaveId);
        $leaveService->reject($leave, $this->rejectionReason, auth()->user());
        $this->reset(['rejectingLeaveId', 'rejectionReason']);
        session()->flash('success', 'Leave rejected.');
    }

    public function render()
    {
        return view('livewire.tenant.hr.leave.index', [
            'staffList' => StaffProfile::with('user')->where('status', 'active')->get(),
            'leaveRequests' => LeaveRequest::with('staffProfile.user')->orderByDesc('created_at')->get(),
        ]);
    }
}
