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

        // if the invite isn't expired, proceed to controller accept method
        if ($invite) {

            if ($invite->accepted_at !== null) {
                $user = User::where('email', $invite->recipient)->first();
                Auth::login($user);

                return redirect()->route('group.index');
            } else {
                return $next($request);
            }
            
        } else {
            // otherwise, invite has expired, show message on registration page
            return Inertia::render('Auth/Register', [
                'status' => 'This invite link has expired. You may either sign up or ask the sender to resend the invite.',
            ]);
        }
    }
}
