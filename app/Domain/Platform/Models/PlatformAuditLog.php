<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PlatformAuditLog extends Model {
    public $timestamps = false;
    protected $fillable = ['admin_id','action','subject_type','subject_id','old_values','new_values','ip_address','user_agent','created_at'];
    protected function casts(): array { return ['old_values'=>'array','new_values'=>'array','created_at'=>'datetime']; }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
}
