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
        return view('emails.invite-to-group');
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
        
            Mail::to($recipient)->send(new InviteToGroup($invite));
            
            ExpireInvite::dispatch($invite)->delay(Carbon::now()->addMinutes(1));
            
            $count++;
        }

        $plural = $count > 1 ? 's' : '';

        return redirect()->route('group.index')->with('status', "{$count} invite{$plural} sent successfully.");
    }

    public function accept($token)
    {
        $invite = Invite::where('token', $token)->first();
     
        // check if the user is already in the group 
        if ($invite->accepted_at) {
            return redirect()->route('debt.index')->with('status', "You are already a member of this group.");
        }

        $group = Group::findOrFail($invite->group_id);
        $user = User::where('email', $invite->recipient)->first();

        if ($user) {
            Auth::login($user);

            GroupUser::create([
                'user_id' => $user->id,
                'group_id' => $invite->group_id,
                'balance' => 0,
            ]);

            $invite->update(['accepted_at' => Carbon::now()]);

            return redirect()->route('group.index')->with('status', "You have successfully joined {$group->name}");
        } else {
            session(['token' => $invite->token]);

            return Inertia::render('Auth/Register', [
                'invite' => $invite,
            ]);
        }
    }
}
