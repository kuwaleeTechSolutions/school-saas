<?php

namespace App\Providers;

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Livewire::addPersistentMiddleware([
            IdentifyTenant::class,
        ]);
    }
}