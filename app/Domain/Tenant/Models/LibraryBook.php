<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBook extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','title','author','isbn','category','publisher'];
    public function copies(): HasMany { return $this->hasMany(LibraryBookCopy::class); }
    public function availableCopiesCount(): int { return $this->copies()->where('status', 'available')->count(); }
}
