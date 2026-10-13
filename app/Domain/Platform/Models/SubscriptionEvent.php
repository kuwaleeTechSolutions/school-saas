<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SubscriptionEvent extends Model {
    protected $fillable = ['subscription_id','event_type','payload','occurred_at'];
    protected function casts(): array { return ['payload'=>'array','occurred_at'=>'datetime']; }
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
}
