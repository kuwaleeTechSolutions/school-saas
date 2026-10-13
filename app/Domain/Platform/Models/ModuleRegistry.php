<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class ModuleRegistry extends Model {
    use HasFactory;
    protected $table = 'modules';
    protected $fillable = ['code','name','category','icon','route','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function features(): HasMany { return $this->hasMany(Feature::class, 'module_id'); }
    public function plans(): BelongsToMany { return $this->belongsToMany(Plan::class, 'plan_modules', 'module_id', 'plan_id'); }
}
