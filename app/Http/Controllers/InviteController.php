<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToGroup;
use App\Models\Group;
use App\Models\Invite;
use App\Http\Requests\InviteToGroupRequest;
use Illuminate\Support\Str;

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

        dump('a', $validated);
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
            
            $count++;
        }

        $plural = $count > 1 ? 's' : '';

        return redirect('/groups')->with('status', "{$count} invite{$plural} sent successfully.");
    }
}
