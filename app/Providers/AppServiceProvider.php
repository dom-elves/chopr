<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Alias;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Comment;
use App\Models\Invite;
use App\Policies\SharePolicy;
use App\Policies\GroupPolicy;
use App\Policies\GroupUserPolicy;
use App\Policies\AliasPolicy;
use App\Policies\DebtPolicy;
use App\Policies\CommentPolicy;
use App\Observers\InviteObserver;
use App\Observers\GroupObserver;


class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Group::class => GroupPolicy::class,
        GroupUser::class => GroupUserPolicy::class,
        Alias::class => AliasPolicy::class,
        Debt::class => DebtPolicy::class,
        Share::class => SharePolicy::class,
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
        Invite::observe(InviteObserver::class);
        Group::observe(GroupObserver::class);
    }
}
