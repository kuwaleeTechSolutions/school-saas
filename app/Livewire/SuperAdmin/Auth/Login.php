<?php
namespace App\Livewire\SuperAdmin\Auth;
use App\Domain\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = ''; public string $password = ''; public bool $remember = false;
    public function login(AuditService $audit): void
    {
        $this->validate(['email'=>['required','email'],'password'=>['required','string']]);
        $throttleKey = 'super-admin-login:' . strtolower($this->email) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many attempts. Try again in {$seconds} seconds."); return;
        }
        if (! Auth::guard('admin')->attempt(['email'=>$this->email,'password'=>$this->password,'status'=>'active'], $this->remember)) {
            RateLimiter::hit($throttleKey, 60); $this->addError('email', 'These credentials do not match our records.'); return;
        }
        RateLimiter::clear($throttleKey);
        request()->session()->regenerate();
        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now()])->save();
        $audit->logPlatform($admin->id, 'admin.login', 'Admin', $admin->id);
        $this->redirect(route('super-admin.dashboard'), navigate: true);
    }
    public function render() { return view('livewire.super-admin.auth.login'); }
}
