<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Http\Resources\GroupResource;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $groups = Inertia::scroll(fn () =>
            GroupResource::collection(
                $request->user()
                    ->groups()
                    ->with(['group_users.user', 'group_users.aliases'])
                    ->paginate(5)
                )
            );

        return Inertia::render('Groups', [
            'groups' => $groups,
            'status' => $request->session()->get('status') ?? null,
        ]);
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
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();
  
        Group::create([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'],
        ]);

        return redirect()->route('group.index')->with('status', 'Group created successfully.');
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
    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        if ($request->user()->cannot('update', $group)) {
            return redirect()->route('group.index')->withErrors(['name' => "You do not have permission to edit this group."]);
        }

        $validated = $request->validated();
 
        $group->update(['name' => $validated['name']]);

        return redirect()->route('group.index')->with('status', 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Group $group)
    {
        if ($request->user()->cannot('delete', $group)) {
            return redirect()->route('group.index')->withErrors(['id' => "You do not have permission to delete this group."]);
        } 

        GroupUser::where('group_id', $group->id)->delete();
        Group::where('id', $group->id)->delete();

        $debts = Debt::where('group_id', $group->id)->get();

        foreach ($debts as $debt) {
            $shares = $debt->shares;

            foreach ($shares as $share) {
                $share->delete();
            }

            $debt->delete();
        }

        return redirect()->route('group.index')->with('status', "Group and {$debts->count()} debts deleted successfully.");
    }
    
}
