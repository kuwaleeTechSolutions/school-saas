<?php
namespace App\Livewire\SuperAdmin\Coupons;
use App\Domain\Platform\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
#[Layout('components.layouts.super-admin')]
class Create extends Component
{
    public string $code = ''; public string $description = ''; public string $discountType = 'percentage'; public float $value = 10;
    public ?int $maxRedemptions = null; public int $maxRedemptionsPerSchool = 1; public string $validFrom = ''; public string $validUntil = '';
    public function mount(): void { $this->authorize('create', Coupon::class); }
    public function save(): void
    {
        $this->authorize('create', Coupon::class);
        $this->validate([
            'code' => ['required','string','max:50','unique:coupons,code'], 'discountType' => ['required','in:percentage,fixed_amount'],
            'value' => ['required','numeric','min:0', $this->discountType === 'percentage' ? 'max:100' : 'max:10000000'],
            'maxRedemptionsPerSchool' => ['required','integer','min:1'], 'validUntil' => ['nullable','date','after_or_equal:validFrom'],
        ]);
        Coupon::create([
            'code'=>$this->code,'description'=>$this->description ?: null,'discount_type'=>$this->discountType,'value'=>$this->value,
            'max_redemptions'=>$this->maxRedemptions ?: null,'max_redemptions_per_school'=>$this->maxRedemptionsPerSchool,
            'valid_from'=>$this->validFrom ?: null,'valid_until'=>$this->validUntil ?: null,'is_active'=>true,'created_by'=>Auth::guard('admin')->id(),
        ]);
        session()->flash('success', 'Coupon created.');
        $this->redirect(route('super-admin.coupons.index'), navigate: true);
    }
    public function render() { return view('livewire.super-admin.coupons.create'); }
}
