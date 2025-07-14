<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToGroup;
use App\Models\Group;

class InviteController extends Controller
{
    public function index()
    {
        // return view('emails.invite');
    }

    public function store(Request $request): RedirectResponse
    {
        $group = Group::findOrFail($request->input('group_id'));
      
        
        foreach ($request->input('recipients') as $recipient) {
            Mail::to($recipient)->send(new InviteToGroup($group));
        }
        
        return redirect('/groups');
    }
}
