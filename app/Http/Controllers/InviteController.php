<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
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
use App\Events\InviteCreated;

class InviteController extends Controller
{
    public function index()
    {

    }

    public function store(InviteToGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $errors = [];

        // check if email belongs to someone already in group
        $users_in_group = Group::findOrFail($validated['group_id'])
            ->users()
            ->whereIn('email', $validated['recipients'])
            ->get();

        if ($users_in_group->isNotEmpty()) {
            $errors['existing'] = 'The following recipients are already in the group: ' . 
                implode(', ', $users_in_group->pluck('email')->toArray());
        }
        
        // check if email belongs to someone with a pending invite to the group
        $existing_user_invites = Invite::whereIn('recipient', $validated['recipients'])
            ->where('group_id', $validated['group_id'])
            ->whereNull('accepted_at')
            ->get();

        if ($existing_user_invites->isNotEmpty()) {
            $errors['pending'] = 'The following recipients have pending invites: ' . 
                implode(', ', $existing_user_invites->pluck('recipient')->toArray());    
        }

        // if errors built, return
        if ($errors) {
            return redirect()
                ->back()
                ->withErrors($errors);
        }

        // not already in group or invited, send invites
        foreach ($validated['recipients'] as $recipient) {
            $invite = Invite::create([
                'group_id' => $validated['group_id'],
                'user_id' => $validated['user_id'],
                'body' => $validated['body'],
                'recipient' => $recipient,
                'token' => Str::random(16),
            ]);

            InviteCreated::dispatch($invite);
        }

        $count = count($validated['recipients']);

        return redirect()
            ->route('group.index')
            ->with([
                'status' => "{$count} " . Str::plural('invite', $count) . " sent successfully."
            ]);
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
