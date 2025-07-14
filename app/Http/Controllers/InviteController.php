<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToGroup;
use App\Models\Group;
use App\Http\Requests\InviteToGroupRequest;

class InviteController extends Controller
{
    public function index()
    {
        // return view('emails.invite');
    }

    public function store(InviteToGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $group = Group::findOrFail($validated['group_id']);
        
        // loop over recipients so mail doesn't stack up in to()
        foreach ($validated['recipients'] as $recipient) {
            Mail::to($recipient)->send(new InviteToGroup($group, $validated['body']));
        }
        
        return redirect('/groups');
    }
}
