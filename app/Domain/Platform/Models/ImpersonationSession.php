<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ImpersonationSession extends Model {
    protected $fillable = ['admin_id','school_id','impersonated_user_id','reason','started_at','ended_at','ip_address'];
    protected function casts(): array { return ['started_at'=>'datetime','ended_at'=>'datetime']; }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
    public function school(): BelongsTo { return $this->belongsTo(\App\Domain\Tenant\Models\School::class); }
    public function isActive(): bool { return $this->ended_at === null; }
}
