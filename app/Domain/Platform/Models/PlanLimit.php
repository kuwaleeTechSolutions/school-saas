<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PlanLimit extends Model {
    protected $fillable = ['plan_id','metric','max_value'];
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
}
