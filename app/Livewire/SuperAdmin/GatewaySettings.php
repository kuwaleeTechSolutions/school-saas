<?php
namespace App\Livewire\SuperAdmin;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use Livewire\Attributes\Layout;
use Livewire\Component;
#[Layout('components.layouts.super-admin')]
class GatewaySettings extends Component
{
    public string $editingProvider = ''; public string $apiKey = ''; public string $apiSecret = ''; public string $webhookSecret = ''; public bool $isTestMode = true;
    public function mount(): void { $this->authorize('viewAny', PaymentGatewayCredential::class); }
    public function edit(string $provider): void
    {
        abort_unless(in_array($provider, ['razorpay','stripe'], true), 404);
        $this->authorize('update', PaymentGatewayCredential::firstOrNew(['provider' => $provider]));
        $this->editingProvider = $provider; $this->apiKey = ''; $this->apiSecret = ''; $this->webhookSecret = ''; $this->isTestMode = true;
    }
    public function save(): void
    {
        abort_unless(in_array($this->editingProvider, ['razorpay','stripe'], true), 404);
        $this->authorize('update', PaymentGatewayCredential::firstOrNew(['provider' => $this->editingProvider]));
        $this->validate(['apiKey'=>['required','string'],'apiSecret'=>['required','string']]);
        PaymentGatewayCredential::updateOrCreate(['provider'=>$this->editingProvider], ['api_key'=>$this->apiKey,'api_secret'=>$this->apiSecret,'webhook_secret'=>$this->webhookSecret ?: null,'is_test_mode'=>$this->isTestMode]);
        $this->reset(['editingProvider','apiKey','apiSecret','webhookSecret']);
        session()->flash('success', 'Gateway credentials saved.');
    }
    public function toggleActive(string $provider): void
    {
        abort_unless(in_array($provider, ['razorpay','stripe'], true), 404);
        $credential = PaymentGatewayCredential::where('provider', $provider)->first();
        $this->authorize('update', $credential ?? PaymentGatewayCredential::firstOrNew(['provider' => $provider]));
        if (! $credential || ! $credential->isConfigured()) { session()->flash('error', 'Configure API credentials before activating this gateway.'); return; }
        PaymentGatewayCredential::where('is_active', true)->update(['is_active' => false]);
        $credential->update(['is_active' => true]);
        session()->flash('success', ucfirst($provider).' is now the active gateway.');
    }
    public function render()
    {
        $providers = ['razorpay','stripe'];
        return view('livewire.super-admin.gateway-settings', ['providers'=>$providers,'credentials'=>PaymentGatewayCredential::whereIn('provider',$providers)->get()->keyBy('provider')]);
    }
}
