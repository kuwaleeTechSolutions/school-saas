<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\LibraryBook;
use App\Models\User;
class LibraryBookPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('library.view'); }
    public function create(User $user): bool { return $this->entitlements->can('library.create'); }
    public function update(User $user, LibraryBook $book): bool { return $this->entitlements->can('library.edit'); }
    public function delete(User $user, LibraryBook $book): bool { return $this->entitlements->can('library.delete'); }
}
