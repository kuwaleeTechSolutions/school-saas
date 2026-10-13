<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Models\User;
class AttendanceRecordPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('attendance.view'); }
    public function create(User $user): bool { return $this->entitlements->can('attendance.create'); }
    public function correct(User $user): bool { return $this->entitlements->can('attendance.correct'); }
}
