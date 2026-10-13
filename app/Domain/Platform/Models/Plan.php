<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Plan extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['ulid','name','code','description','badge','display_order','is_recommended','status','trial_days'];
    protected function casts(): array { return ['is_recommended'=>'boolean']; }
    protected static function booted(): void { static::creating(fn($p) => $p->ulid ??= (string) Str::ulid()); }
    public function prices(): HasMany { return $this->hasMany(PlanPrice::class); }
    public function limits(): HasMany { return $this->hasMany(PlanLimit::class); }
    public function modules(): BelongsToMany { return $this->belongsToMany(ModuleRegistry::class, 'plan_modules', 'plan_id', 'module_id'); }
    public function features(): BelongsToMany { return $this->belongsToMany(Feature::class, 'plan_features', 'plan_id', 'feature_id'); }
    public function permissions(): BelongsToMany { return $this->belongsToMany(Permission::class, 'plan_permissions', 'plan_id', 'permission_id'); }
    public function limitFor(string $metric): ?int { return $this->limits()->where('metric', $metric)->value('max_value'); }
}
