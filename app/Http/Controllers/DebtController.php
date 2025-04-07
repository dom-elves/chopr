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

        $debt = Debt::create([
            'group_id' => $validated['group_id'],
            'user_id' => $user->id,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'split_even' => $validated['split_even'],
            'cleared' => 0,
            'currency' => $validated['currency'],
        ]);

        // we don't rely on model events here
        // as we need to loop over the [user_id => share_amount] kv pairs
        foreach ($validated['user_ids'] as $user_id => $share_amount) {
            // for clarity: $user_id is the id of the user selected fo a newly created share
            // $user is the user creating the debt
            Model::withoutEvents(function() use ($user_id, $debt, $share_amount, $user) {
                $share = Share::create([
                    'debt_id' => $debt->id,
                    'user_id' => $user_id,
                    'amount' => $share_amount,
                    'paid_amount' => 0,
                    'sent' => 0,
                    'seen' => 0,
                ]);

                if ($debt->user_id === $share->user_id) {
                    $user->total_balance += $share_amount;
                } else {
                    $user->total_balance -= $share_amount;
                }
            });
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

        // todo: change this so that rather than division
        // throw an error that says "there is x not accounted for"


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
    public function destroy(Request $request, Debt $debt)
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:debts,id', new IsDebtOwner],
        ])->validate();

        $debt = Debt::findOrFail($validated['id']);

        $debt->delete();
    }
}
