<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Invite;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CheckInviteExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response|\Inertia\Response
    {
        $invite = Invite::where('token', $request->route('token'))->first();
  
        switch ($invite) {
            // invite already accepted, log in & redirect to groups
            case $invite->accepted_at !== null:
                $user = User::where('email', $invite->recipient)->first();
                // todo: look into session tokens properly
                // as currently this is a bit insecure
                // if you send someone your link
                // they can log in as you
                Auth::login($user);

                return redirect()->route('group.index')->with('status', "You have successfully joined {$invite->group->name}");
            // invite is expired, redirect to registration w/message
            case $invite->expired_at !== null:
                return Inertia::render('Auth/Register', [
                'status' => 'This invite link has expired. You may either sign up or ask the sender to resend the invite.',
            ]);
            // invite not accepted, proceed to controller
            case $invite->accepted_at === null:
                return $next($request);
            // default case, just redirect to register
            default:
                return Inertia::render('Auth/Register', [
                'status' => 'An error has occurred. ',
            ]);
        }
    }
}
