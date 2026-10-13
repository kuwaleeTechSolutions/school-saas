<?php
namespace App\Livewire\Tenant\Billing;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Platform\Services\CouponService;
use App\Domain\Platform\Services\UsageMeteringService;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
/**
 * PlatformInvoice has no tenant global scope (Super Admin spans schools), so THIS component is the one place
 * that must filter by the current school explicitly — see TenantBillingScopeTest.
 */
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public string $couponCode = '';
    public function mount(): void { abort_unless(Auth::user()?->hasRole('School Owner'), 403, 'Only the School Owner can access billing.'); }
    public function applyCoupon(CouponService $coupons): void
    {
        abort_unless(Auth::user()?->hasRole('School Owner'), 403);
        $this->validate(['couponCode' => ['required','string','max:50']]);
        try {
            $coupons->validate($this->couponCode, app(TenantContext::class)->school());
            session()->flash('success', "Coupon '{$this->couponCode}' is valid. It is applied when your next invoice is generated; contact support to apply it to an existing unpaid invoice.");
            $this->couponCode = '';
        } catch (RuntimeException $e) { $this->addError('couponCode', $e->getMessage()); }
    }
    public function render(UsageMeteringService $usage)
    {
        $school = app(TenantContext::class)->school();
        return view('livewire.tenant.billing.dashboard', [
            'subscription' => $school->activeSubscription()->with('plan')->first(),
            'invoices' => PlatformInvoice::where('school_id', $school->id)->orderByDesc('due_date')->get(),
            'usage' => $usage->usageFor($school),
        ]);
    }
}
