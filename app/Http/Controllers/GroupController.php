<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Requests\DeleteGroupRequest;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroupController extends Controller
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
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $validated = $request->validated();
  
        $group = Group::create([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'],
        ]);

        $group->save();

        // todo: eventually move this
        GroupUser::create([
            'user_id' => $validated['user_id'],
            'group_id' => $group->id,
        ])->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $validated = $request->validated();
 
        Group::where('id', $validated['id'])->update(['name' => $validated['name']]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteGroupRequest $request, Group $group)
    {
        $validated = $request->validated();

        Group::where('id', $validated['id'])->delete();
        GroupUser::where('group_id', $validated['id'])->delete();
        $debts = Debt::where('user_id', $validated['id'])->get();
        foreach ($debts as $debt) {
            $debt->shares()->delete();
            $debt->delete();
        }
    }
}
