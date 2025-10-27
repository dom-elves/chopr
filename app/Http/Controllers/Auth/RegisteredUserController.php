<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Invite;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        if (session()->has('token')) {

            $invite = Invite::where('token', session('token'))->first();

            $group_user = GroupUser::create([
                'user_id' => $user->id,
                'group_id' => $invite->group_id,
                'balance' => 0
            ]);

            $invite->update([
                'accepted_at' => Carbon::now(),
            ]);

            return redirect()->route('group.index')->with('status', "You have successfully joined {$invite->group->name}");
        } else {
            return redirect(route('debt.index', absolute: false));
        }
        
        // not sure this actually exists, can user it later though
        event(new Registered($user));
    }
}
