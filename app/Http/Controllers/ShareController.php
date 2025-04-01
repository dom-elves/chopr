<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Http\Requests\SendShareRequest;
use Illuminate\Http\Request;
use App\Models\Share;
use App\Models\Debt;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsDebtOwner;
use App\Events\ShareUpdated;

class ShareController extends Controller
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
    public function store(StoreShareRequest $request)
    {
        $validated = $request->validated();

        $share = Share::create([
            'debt_id' => $validated['debt_id'],
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'seen' => 0,
            'sent' => 0,
        ]);

        $debt = Debt::findOrFail($validated['debt_id']);
        $debt->update([
            'amount' => $debt->amount + $validated['amount'],
        ]);

        if ($debt->user_id === $share->user_id) {
            $share->user->total_balance += $validated['amount'];
        } else {
            $share->user->total_balance -= $validated['amount'];
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Share $share)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Share $share)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShareRequest $request)
    {
        // validated data
        $validated = $request->validated();
        // share in question
        $share = Share::findOrFail($validated['id']);
        $original = $share->getOriginal();
        // share attributes before it was updated
        // $changes = $share->getChanges();

        // if we're changing the amount, we also need to update the debt amount
        // todo: change this to an event, possibly in the share listener?
        if (isset($validated['amount'])) { 
            $debt = Debt::find($share->debt_id);
            $debt->update([
                'amount' => $debt->amount - $share->amount + $validated['amount'],
            ]);
        }

        // update the share
        $share->update($validated);
        $changes = $share->getChanges();
        
        dd($changes, $original);
        // fire the event
        // ShareUpdated::dispatch($share->getOriginal(), $changes);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Share $share)
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:shares,id'],
            // has to be called on debt id as we're checking debt ownership
            'debt_id'=> ['required', 'integer', 'exists:debts,id', new IsDebtOwner],
        ])->validate();

        $share = Share::findOrFail($validated['id']);
        $debt = Debt::findOrFail($validated['debt_id']);

        $debt->update([
            'amount' => $debt->amount - $share->amount,
        ]);

        $share->delete();
    }
}
