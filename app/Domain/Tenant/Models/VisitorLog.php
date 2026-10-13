<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VisitorLog extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','visitor_name','visitor_phone','purpose','whom_to_meet','gate_pass_number','checked_in_at','checked_out_at','logged_by'];
    protected function casts(): array { return ['checked_in_at'=>'datetime','checked_out_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn($l) => $l->ulid ??= (string) Str::ulid()); }
    public function isCheckedOut(): bool { return $this->checked_out_at !== null; }
}
