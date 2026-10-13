<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Exam;
use App\Models\User;
class ExamPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('examinations.view'); }
    public function view(User $user, Exam $exam): bool { return $this->entitlements->can('examinations.view'); }
    public function create(User $user): bool { return $this->entitlements->can('examinations.create'); }
    public function update(User $user, Exam $exam): bool { return $this->entitlements->can('examinations.edit'); }
}
