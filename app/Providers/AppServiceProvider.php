<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\GroupUser;
use App\Policies\SharePolicy;
use Illuminate\Support\Facades\Event;
use App\Observers\GroupUserObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
        
        });
    }
}
