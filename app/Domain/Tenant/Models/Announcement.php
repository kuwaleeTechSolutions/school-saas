<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id', 'title', 'body', 'audience_type', 'audience_filters',
        'published_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'audience_filters' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $announcement) {
            $announcement->ulid ??= (string) Str::ulid();
        });
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
