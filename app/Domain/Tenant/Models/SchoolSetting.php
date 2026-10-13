<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class SchoolSetting extends Model { use BelongsToSchool; protected $fillable = ['school_id','category','key','value']; }
