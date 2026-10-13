<?php
namespace App\Livewire\SuperAdmin\PackageBuilder;
use App\Domain\Platform\Models\Plan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.super-admin')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public function render()
    {
        $plans = Plan::query()->with(['prices','modules','limits'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('display_order')->paginate(10);
        return view('livewire.super-admin.package-builder.index', ['plans' => $plans]);
    }
    public function studentLimitFor(Plan $plan): string
    {
        $limit = $plan->limits->firstWhere('metric', 'students');
        if (! $limit) return '—';
        return $limit->max_value === null ? 'Unlimited' : number_format($limit->max_value);
    }
    public function monthlyPriceFor(Plan $plan): string
    {
        $price = $plan->prices->firstWhere('billing_cycle', 'monthly');
        return $price ? $price->currency . ' ' . number_format((float) $price->price) : '—';
    }
}
