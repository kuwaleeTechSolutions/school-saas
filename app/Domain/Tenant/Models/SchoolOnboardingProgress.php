<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class SchoolOnboardingProgress extends Model {
    use BelongsToSchool;
    protected $table = 'school_onboarding_progress';
    protected $fillable = ['school_id','step_code','is_completed','is_skipped','completed_at'];
    protected function casts(): array { return ['is_completed'=>'boolean','is_skipped'=>'boolean','completed_at'=>'datetime']; }
}
