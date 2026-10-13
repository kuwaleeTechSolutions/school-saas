<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class ReportCard extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','exam_id','student_id','snapshot','overall_grade','overall_percent','teacher_remarks','principal_remarks','pdf_path','pdf_status'];
    protected function casts(): array { return ['snapshot'=>'array','overall_percent'=>'decimal:2']; }
    protected static function booted(): void { static::creating(fn($r) => $r->ulid ??= (string) Str::ulid()); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
