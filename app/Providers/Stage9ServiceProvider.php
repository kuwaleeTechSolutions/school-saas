<?php
namespace App\Providers;
use App\Domain\Shared\Ai\AiProviderContract;
use App\Domain\Shared\Ai\DisabledAiProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
class Stage9ServiceProvider extends ServiceProvider
{
    public function register(): void { $this->app->bind(AiProviderContract::class, DisabledAiProvider::class); /* rebind to a real provider later */ }
    public function boot(): void
    {
        RateLimiter::for('api-v1', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ? 'u:'.$r->user()->id : 'ip:'.$r->ip()));
    }
}
