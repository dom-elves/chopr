<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreGroupUserRequest;
use App\Http\Requests\UpdateGroupUserRequest;
use App\Http\Requests\DeleteGroupUserRequest;
use Carbon\Carbon;
use App\Models\GroupUser;

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
     * Store a newly created resource in storage.
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
    public function destroy(DeleteGroupUserRequest $request, GroupUser $groupUser)
    {
        $validated = $request->validated();

        GroupUser::where('id', $validated['group_user_id'])->delete();
    }
}
