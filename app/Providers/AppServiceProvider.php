<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Alias;
use App\Models\Debt;
use App\Models\Comment;
use App\Policies\SharePolicy;
use Illuminate\Support\Facades\Event;
use App\Policies\GroupPolicy;
use App\Policies\GroupUserPolicy;
use App\Policies\AliasPolicy;
use App\Policies\DebtPolicy;
use App\Policies\CommentPolicy;


class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Group::class => GroupPolicy::class,
        GroupUser::class => GroupUserPolicy::class,
        Alias::class => AliasPolicy::class,
        Debt::class => DebtPolicy::class,
        Comment::class => CommentPolicy::class,
    ];
    
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
        // Gate::guessPolicyNamesUsing(function (string $modelClass) {
        
        // });
    }
}
