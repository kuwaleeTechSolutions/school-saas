<?php
namespace Tests\Feature;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use App\Domain\Platform\Models\PlatformPayment;
use App\Domain\Platform\Services\PlatformInvoiceService;
use App\Domain\Platform\Services\PlatformPaymentService;
use App\Domain\Platform\Models\Plan;
use App\Domain\Tenant\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;
use App\Domain\Platform\Models\Admin;

/** Manual payments apply immediately; gateway payments stay pending until confirmed; confirmation is idempotent. */
class PlatformPaymentServiceTest extends TestCase
{
    use RefreshDatabase;
    protected Admin $admin;
    protected School $school; protected $invoice;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = Admin::create([
            'name' => 'Test Platform Admin',
            'email' => 'platform-admin@test.local',
            'password' => 'password',
            'status' => 'active',
        ]);

        [$this->school, $sub] = $this->makeSchool();

        $this->invoice = app(PlatformInvoiceService::class)
            ->generateForPeriod($sub, Carbon::parse('2026-06-01'));
    }
    protected function makeSchool(?string $slug = null): array
    {
        $school = School::factory()->create($slug ? ['slug' => $slug] : []);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $sub = $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        return [$school, $sub];
    }

    /** @test */ public function a_partial_manual_payment_makes_it_partially_paid(): void { app(PlatformPaymentService::class)->recordManualPayment($this->invoice, (float) $this->invoice->total_amount / 2, 'bank_transfer', $this->admin->id); $this->assertSame('partially_paid', $this->invoice->fresh()->status); }
    /** @test */ public function a_full_payment_makes_it_paid(): void { app(PlatformPaymentService::class)->recordManualPayment($this->invoice, (float) $this->invoice->total_amount, 'cheque', $this->admin->id); $i = $this->invoice->fresh(); $this->assertSame('paid', $i->status); $this->assertEquals(0, $i->balance_amount); }
    /** @test */ public function an_overpayment_is_rejected(): void { $this->expectException(RuntimeException::class); app(PlatformPaymentService::class)->recordManualPayment($this->invoice, (float) $this->invoice->total_amount + 500, 'bank_transfer', $this->admin->id); }
    /** @test */ public function manual_recording_rejects_the_gateway_method(): void { $this->expectException(RuntimeException::class); app(PlatformPaymentService::class)->recordManualPayment($this->invoice, 100, 'gateway', $this->admin->id); }
    /** @test */ public function a_gateway_payment_stays_pending_and_the_invoice_unpaid(): void
    {
        PaymentGatewayCredential::create(['provider'=>'razorpay','api_key'=>'k','api_secret'=>'s','is_active'=>true]);
        try { app(PlatformPaymentService::class)->initiateGatewayPayment($this->invoice, 'razorpay'); } catch (RuntimeException $e) { /* expected: no SDK installed, by design */ }
        $p = PlatformPayment::where('platform_invoice_id', $this->invoice->id)->first();
        $this->assertNotNull($p); $this->assertSame('pending', $p->status); $this->assertSame('unpaid', $this->invoice->fresh()->status);
    }
    /** @test */ public function confirmation_applies_once_and_replays_are_no_ops(): void
    {
        $p = PlatformPayment::create(['platform_invoice_id'=>$this->invoice->id,'school_id'=>$this->school->id,'method'=>'gateway','gateway'=>'razorpay','amount'=>$this->invoice->total_amount,'status'=>'pending']);
        $s = app(PlatformPaymentService::class);
        $s->confirmGatewayPayment($p, ['a'=>1]); $this->assertSame('paid', $this->invoice->fresh()->status);
        $s->confirmGatewayPayment($p->fresh(), ['dup'=>1]);
        $i = $this->invoice->fresh(); $this->assertEquals(0, $i->balance_amount); $this->assertEquals($i->total_amount, $i->paid_amount);
    }
}
