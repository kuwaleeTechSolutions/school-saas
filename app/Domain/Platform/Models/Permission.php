<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as SpatiePermission;
class Permission extends SpatiePermission {
    public function module(): BelongsTo { return $this->belongsTo(ModuleRegistry::class, 'module_id'); }
    public function feature(): BelongsTo { return $this->belongsTo(Feature::class, 'feature_id'); }
}
