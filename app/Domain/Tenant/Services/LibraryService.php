<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\LibraryBookCopy;
use App\Domain\Tenant\Models\LibraryFineRule;
use App\Domain\Tenant\Models\LibraryIssue;
use App\Domain\Tenant\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LibraryService
{
    public function __construct(protected AuditService $audit) {}

    public function issueBook(LibraryBookCopy $copy, Student $student, Carbon $dueOn, User $actor): LibraryIssue
    {
        if ($copy->status !== 'available') {
            throw new RuntimeException('This copy is not currently available to issue.');
        }
        return DB::transaction(function () use ($copy, $student, $dueOn, $actor) {
            $issue = LibraryIssue::create([
                'library_book_copy_id' => $copy->id, 'student_id' => $student->id,
                'issued_on' => now()->toDateString(), 'due_on' => $dueOn->toDateString(), 'issued_by' => $actor->id,
            ]);
            $copy->update(['status' => 'issued']);
            $this->audit->logSchool($actor->id, 'library.book_issued', LibraryIssue::class, $issue->id, null, [
                'copy_id' => $copy->id, 'student_id' => $student->id, 'due_on' => $dueOn->toDateString(),
            ]);
            return $issue->fresh();
        });
    }

    public function returnBook(LibraryIssue $issue, User $actor, ?Carbon $asOf = null): LibraryIssue
    {
        if ($issue->returned_on !== null) {
            throw new RuntimeException('This book has already been returned.');
        }
        $asOf = $asOf ?? now();
        $fine = $this->computeFine($issue, $asOf);
        return DB::transaction(function () use ($issue, $actor, $asOf, $fine) {
            $issue->update(['returned_on' => $asOf->toDateString(), 'fine_amount' => $fine]);
            $issue->copy()->update(['status' => 'available']);
            $this->audit->logSchool($actor->id, 'library.book_returned', LibraryIssue::class, $issue->id, null, ['fine_amount' => $fine]);
            return $issue->fresh();
        });
    }

    public function computeFine(LibraryIssue $issue, ?Carbon $asOf = null): float
    {
        $asOf = $asOf ?? now();
        if ($asOf->lte($issue->due_on)) return 0.0;
        $rule = LibraryFineRule::where('school_id', $issue->school_id)->where('is_active', true)->first();
        if (! $rule) return 0.0;
        $daysLate = (int) $issue->due_on->diffInDays($asOf, false);
        $amount = (float) $rule->per_day_amount * $daysLate;
        return $rule->max_amount !== null ? min($amount, (float) $rule->max_amount) : $amount;
    }

    public function waiveFine(LibraryIssue $issue, User $actor): LibraryIssue
    {
        $issue->update(['fine_waived' => true]);
        $this->audit->logSchool($actor->id, 'library.fine_waived', LibraryIssue::class, $issue->id);
        return $issue->fresh();
    }
}
