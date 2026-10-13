<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SubscriptionHistory extends Model {
    public $timestamps = true;
    protected $fillable = ['subscription_id','change_type','old_values','new_values','actor_type','actor_id','reason'];
    protected function casts(): array { return ['old_values'=>'array','new_values'=>'array']; }
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
}
