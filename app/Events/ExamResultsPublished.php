<?php

namespace App\Events;

use App\Domain\Tenant\Models\Exam;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Section 37/41 — fired exactly once, the moment the LAST subject in an
 * exam reaches 'published' (i.e. `$exam->isFullyPublished()` just became
 * true). This is the hook point for Stage 6's
 * SendResultsPublishedNotification listener — mirrors the
 * StudentMarkedAbsent pattern from Stage 3/4 exactly.
 */
class ExamResultsPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public Exam $exam)
    {
    }
}
