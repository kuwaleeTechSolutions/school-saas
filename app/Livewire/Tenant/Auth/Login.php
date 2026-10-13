<?php
namespace App\Livewire\Tenant\Auth;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = ''; public string $password = ''; public bool $remember = false;
    public function login(TenantContext $tenant, AuditService $audit): void
    {
        $this->validate(['email'=>['required','email'],'password'=>['required','string']]);
        $throttleKey = 'tenant-login:' . $tenant->currentSchoolId() . '|' . strtolower($this->email) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many attempts. Try again in {$seconds} seconds."); return;
        }
        if (! Auth::attempt(['email'=>$this->email,'password'=>$this->password,'status'=>'active'], $this->remember)) {
            RateLimiter::hit($throttleKey, 60); $this->addError('email', 'These credentials do not match our records.'); return;
        }
        RateLimiter::clear($throttleKey);
        session()->regenerate();
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();
        $audit->logSchool($user->id, 'user.login', 'User', $user->id);
        $this->redirect(route('tenant.dashboard'), navigate: true);
    }
    public function render() { return view('livewire.tenant.auth.login'); }
}
