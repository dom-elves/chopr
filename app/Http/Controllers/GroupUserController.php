<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreGroupUserRequest;
use App\Http\Requests\UpdateGroupUserRequest;
use Carbon\Carbon;
use App\Models\GroupUser;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsGroupOwner;
use Illuminate\Http\RedirectResponse;

class GroupUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

    }

    /**
     * this is defunct as it's done by invites
     */
    public function store(StoreGroupUserRequest $request)
    {
        // todo: make this an email invite
        $validated = $request->validated();

        GroupUser::create([
            'user_id' => $validated['user_id'],
            'group_id' => $validated['group_id'],
        ])->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(GroupUser $groupUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GroupUser $groupUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupUserRequest $request, GroupUser $groupUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, GroupUser $group_user): RedirectResponse
    {
        $validated = Validator::make($group_user->toArray(), [
            'group_id' => ['required', 'integer', 'exists:groups,id', new IsGroupOwner()],
            'id' => ['required', 'integer', 'exists:group_users,id'],
        ])->validate();
        
        $group_user = GroupUser::findOrFail($validated['id']);
        $group_user->delete();

        return redirect()->route('groups')->with('status', 'Group User deleted successfully.');
    }
}
