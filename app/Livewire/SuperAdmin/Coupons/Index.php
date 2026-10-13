<?php
namespace App\Livewire\SuperAdmin\Coupons;
use App\Domain\Platform\Models\Coupon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
#[Layout('components.layouts.super-admin')]
class Index extends Component
{
    use WithPagination;
    public function mount(): void { $this->authorize('viewAny', Coupon::class); }
    public function deactivate(int $couponId): void
    {
        $coupon = Coupon::findOrFail($couponId);
        $this->authorize('update', $coupon);
        $coupon->update(['is_active' => false]);
    }
    public function render() { return view('livewire.super-admin.coupons.index', ['coupons' => Coupon::withCount('redemptions')->orderByDesc('created_at')->paginate(15)]); }
}
