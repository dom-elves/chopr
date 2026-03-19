<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Group;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use App\Services\ShareService;
use Brick\Money\Money;
use App\Http\Resources\DebtResource;
use App\Http\Resources\GroupResource;
use App\Events\DebtCreated;
use App\Events\DebtUpdated;

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $debts = Inertia::scroll(fn() => 
            DebtResource::collection(
                // query builder to get the debts where the user is the owner
                // or has a share in the debt via group_user
                Debt::whereIn('group_user_id', $user->group_users->pluck('id')->toArray())
                    ->orWhereHas('shares.group_user', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->distinct()
                    ->latest()
                    ->with([
                        'shares.group_user.user:id,name',
                        'comments.group_user.user:id,name',
                        'group.groupUsers.user',
                    ])
                    ->paginate(5)
            )
        );

        $groups = GroupResource::collection(
            $request->user()
                ->groups()
                ->with('groupUsers.user')
                ->get()
        );
            
        return Inertia::render('Debts', [
            'groups' => $groups,
            'debts' => $debts,
            'status' => $request->session()->get('status') ?? null,
        ]);
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
     * DebtCreated is the event which the listener listens for,
     * which then fires the notification.
     */
    public function store(StoreDebtRequest $request, ShareService $shareService): RedirectResponse
    {
        $validated = $request->validated();

        $group = Group::findOrFail($validated['group_id']);

        if ($request->user()->cannot('create', [Debt::class, $group])) {
            return redirect()->route('debt.index')->withErrors(['id' => "You do not have permission to create this debt."]);
        } 

        $debt = Debt::create([
            'group_id' => $group->id,
            'group_user_id' => $validated['group_user_id'],
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'split_even' => $validated['split_even'],
            'cleared' => 0,
            'currency' => $validated['currency'],
        ]);

        $shareService->createDebtShares($validated['user_shares'], $debt);

        DebtCreated::dispatch($debt);

        return redirect()->route('debt.index')->with('status', 'Debt created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Debt $debt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Debt $debt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDebtRequest $request, Debt $debt, ShareService $shareService): RedirectResponse
    {
        if ($request->user()->cannot('update', $debt)) {
            return redirect()->route('debt.index')->withErrors(['id' => "You do not have permission to edit this debt."]);
        } 
        
        // validate data and set original aount
        $validated = $request->validated();

        // calculate from shares rather than debt->amount
        // as user can, in theory, keep editing the amount over and over
        $original_amount = $debt->shares->reduce(function (?Money $carry, Share $share) {
            if ($carry === null) {
                return $share->amount;
            }
            return $carry->plus($share->amount);
        }, null);

        // update data
        $debt->update([
            'name' => $validated['name'],
            'amount' => Money::of($validated['amount'], $debt->currency),
        ]);

        DebtUpdated::dispatch($debt);
        
        // extra bits to do if the amount was changed
        if ($debt->wasChanged('amount')) {
            // the new amount minus the original
            $discrepancy = $debt->amount->minus($original_amount);

            // update split even debt shares if needed
            if ($debt->split_even) {
                $shareService->updateDebtShares($debt, $discrepancy);
            }

            // no use returning the discrepancy on update
            // as we always need to see it on the frontend at any given time
            return redirect()->route('debt.index')->with('status', 'Debt & shares updated successfully.');
        }

        // if just the name has been changed, just return
        return redirect()->route('debt.index')->with('status', 'Debt updated successfully.');
    }
        
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Debt $debt, ShareService $shareService): RedirectResponse
    {
        if ($request->user()->cannot('delete', $debt)) {
            return redirect()->route('debt.index')->withErrors(['id' => "You do not have permission to delete this debt."]);
        } 

        $shareService->deleteDebtShares($debt);

        $debt->delete();

        return redirect()
            ->route('debt.index')
            ->with('status', "Debt deleted successfully.");
    }
    
}
