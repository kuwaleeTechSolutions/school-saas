<?php
namespace Tests\Feature;
use App\Domain\Platform\Models\GatewayWebhookEvent;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use App\Domain\Platform\Models\PlatformPayment;
use App\Domain\Platform\Services\GatewayWebhookService;
use App\Domain\Platform\Services\PlatformInvoiceService;
use App\Domain\Platform\Models\Plan;
use App\Domain\Tenant\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/** Signature verification is real HMAC; events are idempotent on (provider, external_event_id). */
class GatewayWebhookServiceTest extends TestCase
{
    use RefreshDatabase;
    protected School $school; protected $invoice; protected string $secret = 'whsec_test';
    protected function makeSchool(?string $slug = null): array
    {
        $school = School::factory()->create($slug ? ['slug' => $slug] : []);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $sub = $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        return [$school, $sub];
    }

    protected function setUp(): void
    {
        parent::setUp(); $this->seed();
        PaymentGatewayCredential::create(['provider'=>'razorpay','api_key'=>'k','api_secret'=>'s','webhook_secret'=>$this->secret,'is_active'=>true]);
        PaymentGatewayCredential::create(['provider'=>'stripe','api_key'=>'k','api_secret'=>'s','webhook_secret'=>$this->secret,'is_active'=>false]);
        [$this->school, $sub] = $this->makeSchool();
        $this->invoice = app(PlatformInvoiceService::class)->generateForPeriod($sub, Carbon::parse('2026-06-01'));
        PlatformPayment::create(['platform_invoice_id'=>$this->invoice->id,'school_id'=>$this->school->id,'method'=>'gateway','gateway'=>'razorpay','amount'=>$this->invoice->total_amount,'status'=>'pending']);
    }
    protected function rzp(string $event = 'payment.captured', string $id = 'pay_1'): string
    { return json_encode(['event'=>$event,'payload'=>['payment'=>['entity'=>['id'=>$id,'notes'=>['platform_invoice_ulid'=>$this->invoice->ulid]]]]]); }
    protected function sign(string $raw): string { return hash_hmac('sha256', $raw, $this->secret); }
    /** @test */ public function a_valid_signature_is_processed_and_pays_the_invoice(): void
    { $raw = $this->rzp(); $e = app(GatewayWebhookService::class)->handle('razorpay', $raw, $this->sign($raw)); $this->assertSame('processed', $e->status); $this->assertSame('paid', $this->invoice->fresh()->status); }
    /** @test */ public function an_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        try { app(GatewayWebhookService::class)->handle('razorpay', $this->rzp(), 'forged'); $this->fail('Expected exception'); } catch (RuntimeException $e) { $this->assertStringContainsString('signature', $e->getMessage()); }
        $this->assertSame('unpaid', $this->invoice->fresh()->status); $this->assertSame(0, GatewayWebhookEvent::count());
    }
    /** @test */ public function replaying_an_event_never_double_applies(): void
    {
        $raw = $this->rzp('payment.captured', 'pay_dup'); $s = app(GatewayWebhookService::class);
        $s->handle('razorpay', $raw, $this->sign($raw)); $first = $this->invoice->fresh()->paid_amount;
        $s->handle('razorpay', $raw, $this->sign($raw));
        $this->assertEquals($first, $this->invoice->fresh()->paid_amount); $this->assertSame(1, GatewayWebhookEvent::count());
    }
    /** @test */ public function a_failed_event_fails_the_payment_but_not_the_invoice(): void
    {
        $raw = $this->rzp('payment.failed', 'pay_bad'); app(GatewayWebhookService::class)->handle('razorpay', $raw, $this->sign($raw));
        $this->assertSame('unpaid', $this->invoice->fresh()->status); $this->assertDatabaseHas('platform_payments', ['platform_invoice_id'=>$this->invoice->id,'status'=>'failed']);
    }
    /** @test */ public function stripe_signatures_use_the_timestamped_format(): void
    {
        $raw = json_encode(['id'=>'evt_1','type'=>'checkout.session.completed','data'=>['object'=>['id'=>'cs_1','metadata'=>['platform_invoice_ulid'=>$this->invoice->ulid]]]]);
        $t = time(); $sig = "t={$t},v1=".hash_hmac('sha256', $t.'.'.$raw, $this->secret);
        PlatformPayment::where('platform_invoice_id', $this->invoice->id)->update(['gateway'=>'stripe']);
        $e = app(GatewayWebhookService::class)->handle('stripe', $raw, $sig);
        $this->assertSame('processed', $e->status); $this->assertSame('paid', $this->invoice->fresh()->status);
    }
    /** @test */ public function a_malformed_stripe_signature_is_rejected(): void { $this->expectException(RuntimeException::class); app(GatewayWebhookService::class)->handle('stripe', '{}', 'garbage'); }
}
