<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBookCopy extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','library_book_id','accession_number','condition','status'];
    public function book(): BelongsTo { return $this->belongsTo(LibraryBook::class, 'library_book_id'); }
    public function issues(): HasMany { return $this->hasMany(LibraryIssue::class); }
}
