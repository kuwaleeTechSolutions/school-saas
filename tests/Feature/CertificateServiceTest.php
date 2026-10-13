<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\CertificateTemplate;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Services\CertificateService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section 52 — placeholder substitution, QR-token-based (never ID-based)
 * public verification, and that the rendered body is frozen at issue time
 * so editing the template afterward never alters an already-issued
 * certificate.
 */
class CertificateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected Student $student;
    protected User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Queue::fake();

        $this->school = School::factory()->create();
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $this->school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        app(TenantContext::class)->set($this->school);

        $this->student = Student::factory()->create(['school_id' => $this->school->id, 'first_name' => 'Jane', 'last_name' => 'Doe', 'admission_number' => 'ADM-001']);
        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function issuing_substitutes_placeholders_into_the_rendered_body(): void
    {
        $template = CertificateTemplate::create([
            'school_id' => $this->school->id, 'name' => 'Bonafide', 'type' => 'bonafide',
            'body_template' => 'This certifies that {{student_name}} ({{admission_number}}) is enrolled here.', 'is_active' => true,
        ]);

        $service = app(CertificateService::class);
        $certificate = $service->issue($template, $this->student, $this->actor);

        $this->assertStringContainsString('Jane Doe', $certificate->rendered_body);
        $this->assertStringContainsString('ADM-001', $certificate->rendered_body);
    }

    /** @test */
    public function each_certificate_gets_a_unique_qr_token_and_certificate_number(): void
    {
        $template = CertificateTemplate::create(['school_id' => $this->school->id, 'name' => 'Bonafide', 'type' => 'bonafide', 'body_template' => 'Body', 'is_active' => true]);
        $service = app(CertificateService::class);

        $cert1 = $service->issue($template, $this->student, $this->actor);
        $student2 = Student::factory()->create(['school_id' => $this->school->id]);
        $cert2 = $service->issue($template, $student2, $this->actor);

        $this->assertNotEquals($cert1->qr_token, $cert2->qr_token);
        $this->assertNotEquals($cert1->certificate_number, $cert2->certificate_number);
    }

    /** @test */
    public function verify_by_token_finds_the_correct_certificate(): void
    {
        $template = CertificateTemplate::create(['school_id' => $this->school->id, 'name' => 'Bonafide', 'type' => 'bonafide', 'body_template' => 'Body', 'is_active' => true]);
        $service = app(CertificateService::class);
        $certificate = $service->issue($template, $this->student, $this->actor);

        $found = $service->verifyByToken($certificate->qr_token);

        $this->assertNotNull($found);
        $this->assertSame($certificate->id, $found->id);
    }

    /** @test */
    public function verify_by_an_unknown_token_returns_null(): void
    {
        $service = app(CertificateService::class);
        $this->assertNull($service->verifyByToken('nonexistent-token-xyz'));
    }

    /** @test */
    public function editing_the_template_after_issuing_does_not_change_the_already_issued_certificate(): void
    {
        $template = CertificateTemplate::create(['school_id' => $this->school->id, 'name' => 'Bonafide', 'type' => 'bonafide', 'body_template' => 'Original: {{student_name}}', 'is_active' => true]);
        $service = app(CertificateService::class);
        $certificate = $service->issue($template, $this->student, $this->actor);

        $template->update(['body_template' => 'Changed template entirely']);

        $this->assertStringContainsString('Original: Jane Doe', $certificate->fresh()->rendered_body);
    }
}
