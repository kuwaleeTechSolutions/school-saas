<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Timetable;
use App\Models\User;
class TimetablePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('timetable.view'); }
    public function create(User $user): bool { return $this->entitlements->can('timetable.create'); }
    public function update(User $user, Timetable $timetable): bool { return $this->entitlements->can('timetable.edit'); }
    public function publish(User $user, Timetable $timetable): bool { return $this->entitlements->can('timetable.edit'); }
}
