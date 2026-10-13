<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\LibraryIssue;
use App\Models\User;
class LibraryIssuePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('library.view'); }
    public function create(User $user): bool { return $this->entitlements->can('library.create'); }
    public function update(User $user, LibraryIssue $issue): bool { return $this->entitlements->can('library.edit'); }
}
