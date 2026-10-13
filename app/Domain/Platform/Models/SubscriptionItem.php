<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SubscriptionItem extends Model { protected $fillable = ['subscription_id','item_type','item_code','override_value']; public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); } }
