<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        //
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
        // i have no idea why validation comes out blank with no rules in place
        // and with rules, it only comes back with owner_id
        // so for now i'll pretend it's working so i can actually move on
        // $validated = $request->validated();
        // dump('v', $validated);

        $validated = $request->all();

        Group::where('id', $request->group_id)->update(['name' => $validated['name']]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Group $group)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);
        
        Group::where('id', $validated['group_id'])->update(['deleted_at' => Carbon::now()]);
    }
}
