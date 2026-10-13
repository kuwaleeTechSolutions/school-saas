<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class AdmissionApplication extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','admission_enquiry_id','academic_session_id','applicant_first_name','applicant_last_name','applicant_date_of_birth','applicant_gender','guardian_name','guardian_phone','guardian_email','applying_for_class','previous_school','stage','application_fee_amount','application_fee_paid','entrance_test_score','interview_notes','rejection_reason','student_id'];
    protected function casts(): array { return ['applicant_date_of_birth'=>'date','application_fee_amount'=>'decimal:2','application_fee_paid'=>'boolean','entrance_test_score'=>'decimal:2']; }
    protected static function booted(): void { static::creating(fn($a) => $a->ulid ??= (string) Str::ulid()); }
    public function enquiry(): BelongsTo { return $this->belongsTo(AdmissionEnquiry::class, 'admission_enquiry_id'); }
    public function academicSession(): BelongsTo { return $this->belongsTo(AcademicSession::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function documents(): HasMany { return $this->hasMany(AdmissionApplicationDocument::class); }
    public function stageHistories(): HasMany { return $this->hasMany(AdmissionStageHistory::class)->orderBy('created_at'); }
    public function applicantFullName(): string { return trim("{$this->applicant_first_name} {$this->applicant_last_name}"); }
}
