<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Subject extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','name','code'];
    public function classes(): BelongsToMany { return $this->belongsToMany(SchoolClass::class, 'class_subjects', 'subject_id', 'class_id'); }
}
