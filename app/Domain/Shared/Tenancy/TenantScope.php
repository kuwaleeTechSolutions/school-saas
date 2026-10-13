<?php
namespace App\Domain\Shared\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantScope implements \Illuminate\Database\Eloquent\Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);
        if ($context->hasSchool()) {
            $builder->where($model->getTable() . '.school_id', $context->currentSchoolId());
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
    public function extend(Builder $builder): void {}
}
