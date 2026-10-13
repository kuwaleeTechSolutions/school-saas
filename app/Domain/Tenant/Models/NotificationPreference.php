<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'user_id', 'notification_type', 'channel', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
