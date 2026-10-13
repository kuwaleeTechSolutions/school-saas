<?php

namespace App\Policies;

use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function __construct(protected EntitlementServiceContract $entitlements)
    {
    }

    /** Viewing one's own applicable announcements is never package-gated — only authoring is. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->entitlements->can('communication.create');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $this->entitlements->can('communication.edit');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->entitlements->can('communication.delete');
    }
}
