<?php

namespace App\Providers;

use App\Services\Trinity\CommandService;
use App\Services\Trinity\SoapClient;
use Illuminate\Support\ServiceProvider;

class TrinityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SoapClient::class, fn () => SoapClient::makeFromConfig());
        $this->app->singleton(CommandService::class, fn ($app) => new CommandService($app->make(SoapClient::class)));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
