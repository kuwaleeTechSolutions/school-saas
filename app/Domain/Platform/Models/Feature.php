<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Feature extends Model {
    use HasFactory;
    protected $fillable = ['module_id','code','name','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function module(): BelongsTo { return $this->belongsTo(ModuleRegistry::class, 'module_id'); }
    public function permissions(): HasMany { return $this->hasMany(Permission::class, 'feature_id'); }
    public function plans(): BelongsToMany { return $this->belongsToMany(Plan::class, 'plan_features', 'feature_id', 'plan_id'); }
}
