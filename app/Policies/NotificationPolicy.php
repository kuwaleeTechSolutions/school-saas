<?php

namespace App\Policies;

use App\Domain\Tenant\Models\Notification;
use App\Models\User;

/**
 * A user may only ever see/act on their OWN notifications — never
 * package-gated (receiving a notification you're entitled to by role is
 * not a premium feature), but strictly scoped to ownership regardless of
 * role or plan.
 */
class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
