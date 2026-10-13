<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\SchoolDocument;
use App\Models\User;
class SchoolDocumentPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return true; }
    public function create(User $user): bool { return $this->entitlements->can('documents.create'); }
    public function delete(User $user, SchoolDocument $document): bool { return $this->entitlements->can('documents.delete'); }
}
