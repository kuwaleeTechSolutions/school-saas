<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Named `Notification` (singular domain model) rather than relying on
 * Laravel's built-in notifications table/trait — this app already has its
 * own consistent Service + Model pattern (mirroring AuditService,
 * AdmissionWorkflowService, etc.), and a custom model lets every
 * delivery channel's outcome be tracked explicitly via
 * `notification_deliveries` rather than Laravel's framework-owned schema.
 */
class Notification extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'user_id', 'type', 'title', 'body', 'data', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $notification) {
            $notification->ulid ??= (string) Str::ulid();
        });
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
