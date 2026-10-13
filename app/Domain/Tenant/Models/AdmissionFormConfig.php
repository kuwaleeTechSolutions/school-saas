<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class AdmissionFormConfig extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','field_config','required_documents','application_fee'];
    protected function casts(): array { return ['field_config'=>'array','required_documents'=>'array','application_fee'=>'decimal:2']; }
}
