<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdmissionApplicationDocument extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','admission_application_id','document_type','file_path','original_name','is_verified'];
    protected function casts(): array { return ['is_verified'=>'boolean']; }
    public function application(): BelongsTo { return $this->belongsTo(AdmissionApplication::class, 'admission_application_id'); }
}
