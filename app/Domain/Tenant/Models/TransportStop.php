<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportStop extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','transport_route_id','name','pickup_time','sort_order'];
    public function route(): BelongsTo { return $this->belongsTo(TransportRoute::class, 'transport_route_id'); }
}
