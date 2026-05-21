<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupUser;
use App\Models\Group;
use Dotenv\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Services\ShareService;

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
     * Debt & share deletion as well as balance adjustments are handled in the GroupUserObserver.
     *
     * Alias & Comment deletion is handled in the GroupUserObserver.
     * 
     * Fail validation if deleting self and a new group owner is not passed in.
     *
     * If the user is removing themselves from a group & owns the group, allocate the selected
     * user id as group owner.
     */
    public function destroy(Request $request, GroupUser $groupUser, ShareService $shareService): RedirectResponse
    {
        if ($request->user()->cannot('delete', $groupUser)) {
            return redirect()->route('group.index')->withErrors([
                'id' => 'You do not have permission to delete this group user.'
            ]);
        }
        
        if ($groupUser->user->id === $groupUser->group->user_id && $request->user()->can('delete', $groupUser->group)) {
            $validated = $request->validate(
                ['new_owner_group_user_id' => 'required|exists:group_users,id'],
                ['new_owner_group_user_id.required' => 'Please select a new group owner before leaving the group'],
            );

            $new_user = GroupUser::findOrFail($validated['new_owner_group_user_id']);

            Group::findOrFail($groupUser->group_id)->update([
                'user_id' => $new_user->user_id,
            ]);
        }

        DB::transaction(function () use ($groupUser, $shareService) {
            foreach ($groupUser->shares as $share) {
                $shareService->deleteShare($share);
            }

            $groupUser->delete();
        });

        return redirect()->route('group.index')->with('status', 'Group User deleted successfully.');
    }
}
