<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use App\Http\Requests\DeleteDebtRequest;
use App\Http\Requests\StoreShareRequest;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Share;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $debts = Debt::where('group_id', $request->id)
        //     ->with('shares')
        //     ->get();

        // return Inertia::render('Dashboard', [
        //     // add the groups back in here as it's required for the dashboard
        //     // there's probably a better way to do this
        //     // todo: investigate
        //     'groups' => $request->user()->groups,
        //     'debts' => $debts,
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        dump('test');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDebtRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $group_user = GroupUser::where('group_id', $request->group_id)
            ->where('user_id', $user->id)
            ->first();

        $debt = Debt::create([
            'group_id' => $validated['group_id'],
            'user_id' => $group_user->user_id,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'split_even' => $validated['split_even'],
            'cleared' => 0,
            'currency' => $validated['currency'],
        ]);

        // this doesn't belong here but i just need to test this much works
        foreach ($validated['user_ids'] as $user_id => $amount) {
            $share = Share::create([
                'debt_id' => $debt->id,
                'user_id' => $user_id,
                'amount' => $amount,
                'paid_amount' => 0,
                'sent' => 0,
                'seen' => 0,
            ]);
            
            if ($debt->user_id === $share->user_id) {
                $user->total_balance += $amount;
            } else {
                $user->total_balance -= $amount;
            }
        }
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
    public function update(UpdateDebtRequest $request)
    {
        $validated = $request->validated();
        
        // update the debt with the new amount
        $debt = Debt::findOrFail($validated['id']);
        $debt->amount = $validated['amount'];
        $debt->name = $validated['name'];
        $debt->save();

        // update the relevant shares
        // currently this assumes that debts are split even
        // in future this could possibly be a modal that will update individual amounts
        $shares = $debt->shares;
        $split = $debt->amount / $shares->count();

        foreach ($shares as $share) {
            $share->amount = $split;
            $share->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteDebtRequest $request, Debt $debt)
    {
        $validated = $request->validated();

        Debt::where('id', $validated['id'])->delete();
        Share::where('debt_id', $validated['id'])->delete();

        return response()->json(['message' => 'Debt deleted']);
    }
}
