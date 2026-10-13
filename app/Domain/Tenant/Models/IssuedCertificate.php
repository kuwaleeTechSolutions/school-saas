<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class IssuedCertificate extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','certificate_template_id','student_id','certificate_number','qr_token','rendered_body','issued_by','issued_on','pdf_path','pdf_status'];
    protected function casts(): array { return ['issued_on'=>'date']; }
    protected static function booted(): void {
        static::creating(function (self $certificate) {
            $certificate->ulid ??= (string) Str::ulid();
            $certificate->qr_token ??= Str::random(32);
        });
    }
    public function template(): BelongsTo { return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
