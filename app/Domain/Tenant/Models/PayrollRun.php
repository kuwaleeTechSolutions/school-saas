<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PayrollRun extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','year','month','status','generated_by','finalized_at'];
    protected function casts(): array { return ['finalized_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn($r) => $r->ulid ??= (string) Str::ulid()); }
    public function payslips(): HasMany { return $this->hasMany(Payslip::class); }
    public function label(): string { return \Illuminate\Support\Carbon::create($this->year, $this->month, 1)->format('F Y'); }
}
