<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToGroup;
use App\Models\Group;
use App\Models\Invite;
use App\Http\Requests\InviteToGroupRequest;

class InviteController extends Controller
{
    public function index()
    {
        return view('emails.invite-to-group');
    }

    public function store(InviteToGroupRequest $request, Invite $invite): RedirectResponse
    {
        $validated = $request->validated();

        $invite->fill($validated);
     
        // loop over recipients so mail doesn't stack up in to()
        foreach ($validated['recipients'] as $recipient) {
            $invite->recipient = $recipient;
            Mail::to($recipient)->send(new InviteToGroup($invite));
            $invite->save();
        }
        
        return redirect('/groups');
    }
}
