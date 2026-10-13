<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\LibraryBook;
use App\Domain\Tenant\Models\LibraryBookCopy;
use App\Domain\Tenant\Models\LibraryFineRule;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Services\LibraryService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Section 44 — "Issue/return tracking with due dates and fine
 * calculation." Mirrors Stage 5's LateFeeServiceTest design: fine
 * computation is a pure function of (due date, as-of date), never real
 * wall-clock now(), verified against a configurable per-day rate and cap.
 */
class LibraryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected LibraryBookCopy $copy;
    protected Student $student;
    protected User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->school = School::factory()->create();
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $this->school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        app(TenantContext::class)->set($this->school);

        $book = LibraryBook::create(['school_id' => $this->school->id, 'title' => 'Test Book']);
        $this->copy = LibraryBookCopy::create(['school_id' => $this->school->id, 'library_book_id' => $book->id, 'accession_number' => 'ACC-001', 'status' => 'available']);
        $this->student = Student::factory()->create(['school_id' => $this->school->id]);
        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function issuing_a_book_marks_the_copy_as_issued(): void
    {
        $service = app(LibraryService::class);
        $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);

        $this->assertSame('issued', $this->copy->fresh()->status);
    }

    /** @test */
    public function an_already_issued_copy_cannot_be_issued_again(): void
    {
        $service = app(LibraryService::class);
        $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);

        $otherStudent = Student::factory()->create(['school_id' => $this->school->id]);

        $this->expectException(RuntimeException::class);
        $service->issueBook($this->copy->fresh(), $otherStudent, Carbon::parse('2026-06-15'), $this->actor);
    }

    /** @test */
    public function returning_on_time_incurs_no_fine(): void
    {
        $service = app(LibraryService::class);
        LibraryFineRule::create(['school_id' => $this->school->id, 'per_day_amount' => 5, 'is_active' => true]);

        $issue = $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);
        $returned = $service->returnBook($issue, $this->actor, Carbon::parse('2026-06-10'));

        $this->assertEquals(0, $returned->fine_amount);
        $this->assertSame('available', $this->copy->fresh()->status);
    }

    /** @test */
    public function returning_late_incurs_a_fine_per_day(): void
    {
        $service = app(LibraryService::class);
        LibraryFineRule::create(['school_id' => $this->school->id, 'per_day_amount' => 5, 'is_active' => true]);

        $issue = $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);
        $returned = $service->returnBook($issue, $this->actor, Carbon::parse('2026-06-20'));

        $this->assertEquals(25.0, $returned->fine_amount); // 5 days late * 5/day
    }

    /** @test */
    public function fine_is_capped_at_max_amount(): void
    {
        $service = app(LibraryService::class);
        LibraryFineRule::create(['school_id' => $this->school->id, 'per_day_amount' => 5, 'max_amount' => 15, 'is_active' => true]);

        $issue = $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);
        $returned = $service->returnBook($issue, $this->actor, Carbon::parse('2026-06-20')); // 5 days late * 5 = 25, capped to 15

        $this->assertEquals(15.0, $returned->fine_amount);
    }

    /** @test */
    public function a_book_cannot_be_returned_twice(): void
    {
        $service = app(LibraryService::class);
        $issue = $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);
        $service->returnBook($issue, $this->actor);

        $this->expectException(RuntimeException::class);
        $service->returnBook($issue->fresh(), $this->actor);
    }

    /** @test */
    public function waiving_a_fine_sets_the_flag_without_changing_the_amount(): void
    {
        $service = app(LibraryService::class);
        LibraryFineRule::create(['school_id' => $this->school->id, 'per_day_amount' => 5, 'is_active' => true]);

        $issue = $service->issueBook($this->copy, $this->student, Carbon::parse('2026-06-15'), $this->actor);
        $returned = $service->returnBook($issue, $this->actor, Carbon::parse('2026-06-20'));
        $waived = $service->waiveFine($returned, $this->actor);

        $this->assertTrue($waived->fine_waived);
        $this->assertEquals(25.0, $waived->fine_amount);
    }
}
