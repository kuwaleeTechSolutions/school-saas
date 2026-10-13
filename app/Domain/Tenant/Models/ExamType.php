<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class ExamType extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','weightage_percent'];
    protected function casts(): array { return ['weightage_percent'=>'decimal:2']; }
}
