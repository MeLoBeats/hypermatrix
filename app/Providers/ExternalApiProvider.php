<?php

namespace App\Providers;

use App\Services\HyperplanningRestService;
use App\Services\MatrixService;
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
        $this->app->singleton(MatrixService::class, function () {
            return new MatrixService(config('matrix.base_url'));
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
