<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Platform\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class School extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['ulid','school_code','name','slug','email','phone','alternate_phone','website','logo','address','city','district','state','country','pincode','board','affiliation_number','udise_code','established_year','principal_name','status','onboarding_status','timezone','currency','current_academic_session_id'];
    protected function casts(): array { return ['established_year'=>'integer']; }
    protected static function booted(): void { static::creating(fn($s) => $s->ulid ??= (string) Str::ulid()); }
    public function settings(): HasMany { return $this->hasMany(SchoolSetting::class); }
    public function onboardingProgress(): HasMany { return $this->hasMany(SchoolOnboardingProgress::class); }
    public function usageCounters(): HasMany { return $this->hasMany(SchoolUsageCounter::class); }
    public function auditLogs(): HasMany { return $this->hasMany(SchoolAuditLog::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function activeSubscription(): HasOne { return $this->hasOne(Subscription::class)->where('is_active', true); }
    public function users(): HasMany { return $this->hasMany(\App\Models\User::class); }
    public function academicSessions(): HasMany { return $this->hasMany(AcademicSession::class); }
    public function activeAcademicSession(): ?AcademicSession { return $this->academicSessions()->where('is_current', true)->first(); }
    public function getSetting(string $category, string $key, mixed $default = null): mixed { return $this->settings()->where('category',$category)->where('key',$key)->value('value') ?? $default; }
    protected static function newFactory()
        {
            return \Database\Factories\SchoolFactory::new();
        }
}
