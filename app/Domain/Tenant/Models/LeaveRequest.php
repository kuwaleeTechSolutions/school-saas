<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LeaveRequest extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','staff_profile_id','starts_on','ends_on','reason','status','approved_by','rejection_reason'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date']; }
    protected static function booted(): void { static::creating(fn($l) => $l->ulid ??= (string) Str::ulid()); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function dayCount(): int { return $this->starts_on->diffInDays($this->ends_on) + 1; }
}
