<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Homework;
use App\Models\User;
class HomeworkPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('homework.view'); }
    public function create(User $user): bool { return $this->entitlements->can('homework.create'); }
    public function update(User $user, Homework $homework): bool { return $this->entitlements->can('homework.edit'); }
    public function grade(User $user, Homework $homework): bool { return $this->entitlements->can('homework.edit'); }
}
