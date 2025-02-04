<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    /**
     * Basic search, seems that Inertia won't allow get requests to
     * be returned without it being in an Inertia page, I just want this to go to 
     * a specific component but I'm assuming I'm wrong
     */
    public function index(Request $request)
    {
        $query = User::query();
        $query_string = $request['query_string'];
        $group_id = $request['group_id'];
        $query->where(function ($q) use ($query_string) {
            $q->where('name', 'like', "%$query_string%")
              ->orWhere('email', 'like', "%$query_string%");
        })->whereDoesntHave('groups', function ($q) use ($group_id) {
            $q->where('group_id', $group_id);
        });
                
        $users = $query->paginate(10);

        return response()->json($users);
    }
}
