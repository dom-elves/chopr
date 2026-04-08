<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Group;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Comment;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => function () use ($request) {
                    return $request->user()?->fresh()->only('id', 'name', 'balance');
                },
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'notifications' => $user ? $user->unreadNotifications : [],
        ]);
    }
}
