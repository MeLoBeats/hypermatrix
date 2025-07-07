<?php

namespace App\Providers;

use App\Services\HyperplanningRestService;
use Illuminate\Support\ServiceProvider;

class ExternalApiProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(HyperplanningRestService::class, function () {
            return new HyperplanningRestService(
                config('hyperplanning.login'),
                config('hyperplanning.pass'),
                config('hyperplanning.base_url'),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
