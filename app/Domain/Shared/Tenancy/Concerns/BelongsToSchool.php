<?php
namespace App\Domain\Shared\Tenancy\Concerns;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Shared\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new TenantScope());
        static::creating(function ($model) {
            if (empty($model->school_id)) $model->school_id = app(TenantContext::class)->currentSchoolId();
        });
    }
    public function school(): BelongsTo { return $this->belongsTo(\App\Domain\Tenant\Models\School::class); }
}
