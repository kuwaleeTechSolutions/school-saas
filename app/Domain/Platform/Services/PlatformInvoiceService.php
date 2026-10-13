<?php
namespace App\Domain\Platform\Services;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Platform\Models\PlatformInvoiceLineItem;
use App\Domain\Platform\Models\Subscription;
use App\Domain\Shared\Audit\AuditService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
/** One invoice per (subscription, period). Idempotent — mirrors Stage 5's InvoiceGenerationService. */
class PlatformInvoiceService
{
    public function __construct(protected AuditService $audit, protected CouponService $coupons) {}
    public function generateForPeriod(Subscription $subscription, Carbon $periodStartsOn, ?string $couponCode = null): PlatformInvoice
    {
        $existing = PlatformInvoice::where('subscription_id', $subscription->id)->where('period_starts_on', $periodStartsOn->toDateString())->first();
        if ($existing) return $existing;
        $price = $subscription->plan->prices()->where('billing_cycle', $subscription->billing_cycle)->first();
        if (! $price) throw new RuntimeException("No price configured for plan '{$subscription->plan->name}' at billing cycle '{$subscription->billing_cycle}'.");
        $periodEndsOn = $this->periodEndFor($periodStartsOn, $subscription->billing_cycle);
        $base = (float) $price->price;
        return DB::transaction(function () use ($subscription, $periodStartsOn, $periodEndsOn, $base, $couponCode) {
            $invoice = PlatformInvoice::create([
                'school_id'=>$subscription->school_id,'subscription_id'=>$subscription->id,'invoice_number'=>$this->nextNumber(),
                'period_starts_on'=>$periodStartsOn->toDateString(),'period_ends_on'=>$periodEndsOn->toDateString(),
                'due_date'=>$periodStartsOn->copy()->addDays(7)->toDateString(),
                'subtotal_amount'=>$base,'discount_amount'=>0,'tax_amount'=>0,'total_amount'=>$base,'paid_amount'=>0,'balance_amount'=>$base,'status'=>'unpaid',
            ]);
            PlatformInvoiceLineItem::create(['platform_invoice_id'=>$invoice->id,'description'=>"{$subscription->plan->name} Plan — ".ucfirst($subscription->billing_cycle),'line_type'=>'plan_base','amount'=>$base]);
            if ($couponCode) {
                $redemption = $this->coupons->apply($couponCode, $subscription->school, $subscription, $base);
                PlatformInvoiceLineItem::create(['platform_invoice_id'=>$invoice->id,'description'=>"Coupon: {$redemption->coupon->code}",'line_type'=>'discount','amount'=>-$redemption->discount_applied]);
                $invoice->discount_amount = $redemption->discount_applied;
                $invoice->total_amount = max(0, $base - (float) $redemption->discount_applied);
                $invoice->balance_amount = $invoice->total_amount;
                $invoice->coupon_redemption_id = $redemption->id;
                $invoice->save();
            }
            $this->audit->logPlatform(null, 'platform_invoice.generated', PlatformInvoice::class, $invoice->id, null, ['school_id'=>$subscription->school_id,'amount'=>$base]);
            return $invoice->fresh('lineItems');
        });
    }
    protected function periodEndFor(Carbon $start, string $cycle): Carbon
    {
        return match ($cycle) {
            'quarterly' => $start->copy()->addMonths(3)->subDay(), 'half_yearly' => $start->copy()->addMonths(6)->subDay(),
            'annual' => $start->copy()->addYear()->subDay(), default => $start->copy()->addMonth()->subDay(),
        };
    }
    protected function nextNumber(): string { return 'PINV'.now()->format('y').'-'.str_pad((string) (PlatformInvoice::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT); }
}
