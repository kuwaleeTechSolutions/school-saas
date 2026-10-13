<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\FeeComponent;
use App\Domain\Tenant\Models\FeeInstallment;
use App\Domain\Tenant\Models\FeeInstallmentPlan;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\FeeStructureItem;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentFeeAssignment;
use App\Domain\Tenant\Services\InvoiceGenerationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Section I.4 / O.2 — the mandatory tenant isolation test applied to
 * Stage 5's new module (Fees/Invoices), per Section M.2's per-module
 * requirement.
 */
class Stage5TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected User $ownerA;
    protected FeeInvoice $invoiceB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Queue::fake();

        $this->schoolA = $this->buildSchool('stage5-school-a');
        $this->schoolB = $this->buildSchool('stage5-school-b');

        app(TenantContext::class)->set($this->schoolA);
        $this->ownerA = User::factory()->forSchool($this->schoolA)->create();
        $ownerRole = Role::where('name', 'School Owner')->whereNull('school_id')->first();
        $this->ownerA->assignRole($ownerRole);

        app(TenantContext::class)->set($this->schoolB);
        $sessionB = AcademicSession::where('school_id', $this->schoolB->id)->first();
        $classB = SchoolClass::where('school_id', $this->schoolB->id)->first();
        $sectionB = Section::where('class_id', $classB->id)->first();

        $studentB = Student::factory()->create(['school_id' => $this->schoolB->id]);
        $studentB->enrollments()->create([
            'school_id' => $this->schoolB->id, 'academic_session_id' => $sessionB->id,
            'class_id' => $classB->id, 'section_id' => $sectionB->id, 'status' => 'active',
        ]);

        $structureB = FeeStructure::create(['school_id' => $this->schoolB->id, 'academic_session_id' => $sessionB->id, 'name' => 'Default', 'is_active' => true]);
        $componentB = FeeComponent::create(['school_id' => $this->schoolB->id, 'name' => 'Tuition', 'category' => 'tuition']);
        FeeStructureItem::create(['school_id' => $this->schoolB->id, 'fee_structure_id' => $structureB->id, 'fee_component_id' => $componentB->id, 'annual_amount' => 5000]);

        $planB = FeeInstallmentPlan::create(['school_id' => $this->schoolB->id, 'fee_structure_id' => $structureB->id, 'name' => 'Annual', 'frequency' => 'annual']);
        $installmentB = FeeInstallment::create(['school_id' => $this->schoolB->id, 'fee_installment_plan_id' => $planB->id, 'installment_number' => 1, 'label' => 'Full', 'percentage_of_total' => 100, 'due_date' => now()->addDays(30)]);

        StudentFeeAssignment::create(['school_id' => $this->schoolB->id, 'student_id' => $studentB->id, 'academic_session_id' => $sessionB->id, 'fee_structure_id' => $structureB->id, 'fee_installment_plan_id' => $planB->id]);

        $actorB = User::factory()->forSchool($this->schoolB)->create();
        $this->invoiceB = app(InvoiceGenerationService::class)->generateForInstallment($studentB, $installmentB->id, $actorB);
    }

    protected function buildSchool(string $slug): School
    {
        $school = School::factory()->create(['slug' => $slug]);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        app(TenantContext::class)->set($school);
        $session = AcademicSession::factory()->current()->create(['school_id' => $school->id]);
        $class = SchoolClass::factory()->create(['school_id' => $school->id, 'academic_session_id' => $session->id]);
        Section::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);

        return $school;
    }

    /** @test */
    public function fee_invoices_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(FeeInvoice::find($this->invoiceB->id));
        $this->assertNotContains($this->invoiceB->id, FeeInvoice::pluck('id')->all());
    }

    /** @test */
    public function viewing_another_schools_invoice_by_guessed_id_404s(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->actingAs($this->ownerA);

        $this->get("http://{$this->schoolA->slug}.localhost/fees/invoices/{$this->invoiceB->id}")
            ->assertNotFound();
    }

    /** @test */
    public function fee_structures_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertCount(0, FeeStructure::all());
    }
}
