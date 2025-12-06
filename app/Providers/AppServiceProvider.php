<?php

declare(strict_types=1);

namespace App\Providers;

use Framework\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register application-specific bindings here
        // Example:
        // $this->app->singleton(SomeService::class, function () {
        //     return new SomeService();
        // });
    }

    public function boot(): void
    {
        // Boot application-specific services here
        // This method is called after all service providers are registered
    }
}

