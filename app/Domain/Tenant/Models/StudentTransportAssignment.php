<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransportAssignment extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','student_id','academic_session_id','transport_route_id','transport_stop_id'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function route(): BelongsTo { return $this->belongsTo(TransportRoute::class, 'transport_route_id'); }
    public function stop(): BelongsTo { return $this->belongsTo(TransportStop::class, 'transport_stop_id'); }
}
