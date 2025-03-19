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

        $group_user_ids = $user ? GroupUser::where('user_id', $user->id)->pluck('id')->toArray() : [];

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
            ],
            'ownership' => [
                // groups that the logged in user owns
                'group_ids' => $user ? Group::where('user_id', $user->id)->pluck('id')->toArray() : [],
                // debt ids owned by the logged in user
                'debt_ids' => !empty($group_user_ids) ? Debt::whereIn('user_id', $group_user_ids)->pluck('id')->toArray() : [],
                'comment_ids' => $user ? Comment::where('user_id', $user->id)->pluck('id')->toArray() : [],
            ]
        ]);
    }
}
