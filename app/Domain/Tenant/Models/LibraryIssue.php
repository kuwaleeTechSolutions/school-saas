<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryIssue extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','library_book_copy_id','student_id','issued_on','due_on','returned_on','fine_amount','fine_waived','issued_by'];
    protected function casts(): array {
        return ['issued_on'=>'date','due_on'=>'date','returned_on'=>'date','fine_amount'=>'decimal:2','fine_waived'=>'boolean'];
    }
    public function copy(): BelongsTo { return $this->belongsTo(LibraryBookCopy::class, 'library_book_copy_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function isOverdue(): bool { return $this->returned_on === null && $this->due_on->isPast(); }
}
