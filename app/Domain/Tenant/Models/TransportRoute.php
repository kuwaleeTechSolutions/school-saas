<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportRoute extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','name','vehicle_number','driver_name','driver_phone','monthly_fee','is_active'];
    protected function casts(): array { return ['monthly_fee'=>'decimal:2','is_active'=>'boolean']; }
    public function stops(): HasMany { return $this->hasMany(TransportStop::class)->orderBy('sort_order'); }
    public function assignments(): HasMany { return $this->hasMany(StudentTransportAssignment::class); }
}
