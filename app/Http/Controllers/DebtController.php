<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use App\Http\Requests\StoreShareRequest;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Share;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsDebtOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use App\Services\DebtService;
use App\Services\ShareService;
use Brick\Money\Money;

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // todo: look up if it's better to send data like this
        // or to send in separate variables e.g. list of debts, groups etc
        // with minimal relationships, then map everything together on the FE

        $user = $request->user();

        // relationships for debts
        $relationships = [
            'shares.group_user.user',
            'comments.user',
            'group.group_users.user',
        ];

        // debts owned
        $debts = Debt::where('user_id', $user->id)
            ->orWhereHas('shares', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with($relationships)
            ->paginate(5);
        
        // just groups
        $groups = $request->user()
            ->groups()
            ->with('group_users.user')
            ->get();

        // dd($debts);    

        return Inertia::render('Debts', [
            // 'groups' => $groups,
            'debts' => $debts->sortByDesc('created_at')->values(),
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
     */
    public function store(StoreDebtRequest $request, ShareService $shareService): RedirectResponse
    {
        $validated = $request->validated();
    
        $debt = Debt::create([
            'group_id' => $validated['group_id'],
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'amount' => Money::of($validated['amount'], $validated['currency']),
            'split_even' => $validated['split_even'],
            'cleared' => 0,
            'currency' => $validated['currency'],
        ]);

        $shareService->createDebtShares($validated['user_shares'], $debt);

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
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:debts,id', new IsDebtOwner],
        ])->validate();

        $shareService->deleteDebtShares($debt);

        $debt->delete();

        return redirect()->route('debt.index')->with('status', "Debt deleted successfully.");;
    }
}
