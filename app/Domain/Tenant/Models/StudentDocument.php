<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentDocument extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','student_id','type','file_path','original_name','uploaded_by','expires_on'];
    protected function casts(): array { return ['expires_on'=>'date']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
