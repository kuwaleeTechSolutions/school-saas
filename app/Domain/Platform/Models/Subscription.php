<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class Subscription extends Model {
    protected $fillable = ['ulid','school_id','plan_id','billing_cycle','status','trial_ends_at','current_period_starts_at','current_period_ends_at','grace_period_ends_at','cancellation_effective_at','is_active'];
    protected function casts(): array { return ['trial_ends_at'=>'datetime','current_period_starts_at'=>'datetime','current_period_ends_at'=>'datetime','grace_period_ends_at'=>'datetime','cancellation_effective_at'=>'datetime','is_active'=>'boolean']; }
    protected static function booted(): void { static::creating(fn($s) => $s->ulid ??= (string) Str::ulid()); }
    public function school(): BelongsTo { return $this->belongsTo(\App\Domain\Tenant\Models\School::class); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function items(): HasMany { return $this->hasMany(SubscriptionItem::class); }
    public function histories(): HasMany { return $this->hasMany(SubscriptionHistory::class); }
    public function events(): HasMany { return $this->hasMany(SubscriptionEvent::class); }
    public function allowsOperationalAccess(): bool { return in_array($this->status, ['trial','active','past_due','grace_period','scheduled_cancellation'], true); }
}
