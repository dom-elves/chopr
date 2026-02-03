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
        if (!$request->route('invite')->expired_at) {
            return $next($request);
        } else {
            return Inertia::render('Auth/Register', [
                'status' => 'This invite has expired. You may either sign up or ask the sender to resend the invite.',
            ]);
        }
    }
}
