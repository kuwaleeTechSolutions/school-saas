<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\LeaveRequest;
use App\Domain\Tenant\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use RuntimeException;

class LeaveService
{
    public function __construct(protected AuditService $audit) {}

    public function request(StaffProfile $staff, Carbon $startsOn, Carbon $endsOn, string $reason, User $actor): LeaveRequest
    {
        if ($endsOn->lt($startsOn)) {
            throw new RuntimeException('End date must be on or after the start date.');
        }
        $leave = LeaveRequest::create([
            'staff_profile_id' => $staff->id, 'starts_on' => $startsOn->toDateString(),
            'ends_on' => $endsOn->toDateString(), 'reason' => $reason, 'status' => 'requested',
        ]);
        $this->audit->logSchool($actor->id, 'leave.requested', LeaveRequest::class, $leave->id);
        return $leave;
    }

    public function approve(LeaveRequest $leave, User $actor): LeaveRequest
    {
        if ($leave->status !== 'requested') {
            throw new RuntimeException("Only a 'requested' leave can be approved (current status: '{$leave->status}').");
        }
        $leave->update(['status' => 'approved', 'approved_by' => $actor->id]);
        $this->audit->logSchool($actor->id, 'leave.approved', LeaveRequest::class, $leave->id);
        return $leave->fresh();
    }

    public function reject(LeaveRequest $leave, string $reason, User $actor): LeaveRequest
    {
        if ($leave->status !== 'requested') {
            throw new RuntimeException("Only a 'requested' leave can be rejected (current status: '{$leave->status}').");
        }
        $leave->update(['status' => 'rejected', 'approved_by' => $actor->id, 'rejection_reason' => $reason]);
        $this->audit->logSchool($actor->id, 'leave.rejected', LeaveRequest::class, $leave->id);
        return $leave->fresh();
    }
}
