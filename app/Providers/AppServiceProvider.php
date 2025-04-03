<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Share;
use App\Policies\SharePolicy;
use Illuminate\Support\Facades\Event;
use App\Listeners\UpdateUserTotalBalance;
use App\Events\ShareUpdated;
use App\Events\ShareDeleted;
use App\Events\ShareCreated;
use App\Observers\ShareObserver;

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

        Share::observe(ShareObserver::class);
    }
}
