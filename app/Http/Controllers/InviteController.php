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
        return view('emails.invite-to-group');
    }

    public function store(InviteToGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // check for existing pending invites
        $existing_invites = Invite::whereIn('recipient', $validated['recipients'])
            ->where('group_id', $validated['group_id'])
            ->get();

        // if there are any, return recipient error
        if ($existing_invites) {

            // partition into accepted & pending
            [$accepted, $pending] = $existing_invites->partition(
                fn ($invite) => !is_null($invite->accepted_at)
            );

            $accepted = $accepted->pluck('recipient')->toArray();
            $pending = $pending->pluck('recipient')->toArray();

            return redirect()
                ->back()
                ->withErrors([
                    'recipients.pending' => 'Pending invites exist for: ' . implode(', ', $pending),
                    'recipients.accepted' => 'Users already in the group: ' . implode(', ', $accepted),
                ]);
        }

        // so if all recipients don't have pending invites, send them
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

        // return with success message
        $count = count($validated['recipients']);

        return redirect()
            ->route('group.index')
            ->with([
                'status' => "{$count} " . Str::plural('invite', $count) . " sent successfully."
            ]);
    }

    public function accept($token)
    {
        // find invite & invited user
        $invite = Invite::where('token', $token)->first();
        $user = User::where('email', $invite->recipient)->first();

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
