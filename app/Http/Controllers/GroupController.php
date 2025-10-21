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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsGroupOwner;
use Inertia\Inertia;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         $groups = $request->user()
            ->groups()
            ->with('group_users.user')
            ->get();
        
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
        $validated = $request->validated();
 
        Group::where('id', $validated['id'])->update(['name' => $validated['name']]);

        return redirect()->route('group.index')->with('status', 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Group $group)
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:groups,id', new IsGroupOwner],
        ])->validate();

        GroupUser::where('group_id', $validated['id'])->delete();
        Group::where('id', $validated['id'])->delete();

        $debts = Debt::where('group_id', $validated['id'])->get();

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
