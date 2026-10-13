<?php
namespace App\Domain\Tenant\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdmissionStageHistory extends Model {
    public $timestamps = false;
    protected $fillable = ['school_id','admission_application_id','from_stage','to_stage','changed_by','notes','created_at'];
    protected function casts(): array { return ['created_at'=>'datetime']; }
    public function application(): BelongsTo { return $this->belongsTo(AdmissionApplication::class, 'admission_application_id'); }
}
