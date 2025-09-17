<?php

namespace App\Providers;

use App\Services\SyncMatrixPersonService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SyncMatrixPersonService::class, fn() => new SyncMatrixPersonService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
