<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Mail\InviteToGroup;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\InviteToGroupRequest;
use App\Http\Requests\AcceptInviteRequest;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Jobs\ExpireInvite;

class InviteController extends Controller
{
    public function index()
    {

    }

    public function store(InviteToGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $count = 0;

        // loop over recipients so mail doesn't stack up in to()
        foreach ($validated['recipients'] as $recipient) {

            $invite = Invite::create([
                'group_id' => $validated['group_id'],
                'user_id' => $validated['user_id'],
                'body' => $validated['body'],
                'recipient' => $recipient,
                'token' => Str::random(16),
            ]);
        
            Mail::to($recipient)->queue(new InviteToGroup($invite));
            
            ExpireInvite::dispatch($invite)->delay(Carbon::now()->addDays(1));
            
            $count++;
        }

        $plural = $count > 1 ? 's' : '';

        return redirect()->route('group.index')->with('status', "{$count} invite{$plural} sent successfully.");
    }

    public function accept()
    {
        $user = Auth::user();
        // if they exist as a user but not in this group, add them to the group
        // also updated accepted_at & expire the invite
        if ($user) {
            Auth::login($user);

            GroupUser::create([
                'user_id' => $user->id,
                'group_id' => $invite->group_id,
                'balance' => 0,
            ]);

            $invite->update(['accepted_at' => Carbon::now()]);
            
            return redirect()->route('group.index')->with('status', "You have successfully joined {$invite->group->name}");
        } else {
            // so if they're a new user, store the token in the session
            // and populate the register with their invite info (just email address)
            session(['token' => $invite->token]);

            return Inertia::render('Auth/Register', [
                'invite' => $invite,
            ]);
        }
    }
}
