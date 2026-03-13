<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupUser;
use App\Models\Group;
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
    public function store(Request $request)
    {

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
    public function update(GroupUser $groupUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * Share deletion & balance adjustments are handled in the GroupUserObserver.
     *
     * Alias & Comment deletion is handled in the GroupUserObserver.
     *
     * If the user is removing themselves from a group, allocate the selected
     * user id as group owner.
     */
    public function destroy(Request $request, GroupUser $group_user): RedirectResponse
    {
        if ($request->user()->cannot('delete', $group_user)) {
            return redirect()->route('group.index')->withErrors(['id' => 'You do not have permission to delete this group user.']);
        } 
            
        $group_user->delete();

        if ($request->get('new_owner')) {
            Group::findOrFail($group_user->group_id)->update([
                'user_id' => $request->get('new_owner'),
            ]);
        }

        return redirect()->route('group.index')->with('status', 'Group User deleted successfully.');
    }
}
