<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
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
     * GroupUser for group creator is created in GroupObserver.
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
     *
     * GroupUser deletion is handled in the GroupObserver.
     * Alias deletion is then handled in the GroupUserObserver.
     *
     * Debt deletion is handled in the GroupObserver.
     * Share deletion & Comment deletion are then handled in the DebtOberserver.
     */
    public function destroy(Request $request, Group $group)
    {
        if ($request->user()->cannot('delete', $group)) {
            return redirect()->route('group.index')->withErrors(['id' => "You do not have permission to delete this group."]);
        } 

        $debts_count = Debt::where('group_id', $group->id)->count();

        $group->delete();

        return redirect()->route('group.index')->with('status', "Group and {$debts_count} debts deleted successfully.");
    }
    
}
