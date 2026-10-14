<?php

namespace App\Livewire\Tenant\Auth;

use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    #[Locked]
    public string $schoolSlug = '';

    #[Locked]
    public bool $pathBasedTenant = false;

    public function mount(TenantContext $tenant): void
    {
        $this->schoolSlug = $tenant->school()->slug;

        $this->pathBasedTenant = request()->route('school_slug') !== null
            && request()->route()->getDomain() === null;
    }

    public function login(TenantContext $tenant, AuditService $audit): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey =
            'tenant-login:' .
            $tenant->currentSchoolId() .
            '|' .
            strtolower($this->email) .
            '|' .
            request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError(
                'email',
                "Too many attempts. Try again in {$seconds} seconds."
            );

            return;
        }

        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
            'status' => 'active',
        ], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->addError(
                'email',
                'These credentials do not match our records.'
            );

            return;
        }

        RateLimiter::clear($throttleKey);

        session()->regenerate();

        $user = Auth::user();

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $audit->logSchool(
            $user->id,
            'user.login',
            'User',
            $user->id
        );

        if ($this->pathBasedTenant) {
            $this->redirect(
                '/school/' . $this->schoolSlug . '/dashboard'
            );
        
            return;
        }
        
        $this->redirect(
            route('tenant.dashboard')
        );
    }

    public function render()
    {
        return view('livewire.tenant.auth.login');
    }
}