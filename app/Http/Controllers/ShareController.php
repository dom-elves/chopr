<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Http\Requests\SendShareRequest;
use App\Http\Requests\DeleteShareRequest;
use Illuminate\Http\Request;
use App\Models\Share;
use App\Models\Debt;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsDebtOwner;

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
        //
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
    public function update(UpdateShareRequest $request, Share $share)
    {
        $validated = $request->validated();

        // if we're changing the amount, need to do a few other bits first
        if (isset($validated['amount'])) { 
            $original_share = Share::find($validated['id']);

            $debt = Debt::find($original_share->debt_id);
            $debt->update([
                'amount' => $debt->amount - $original_share->amount + $validated['amount'],
            ]);
        }
        
        // update the share
        Share::where('id', $validated['id'])->update($validated);   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Share $share)
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:shares,id'],
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
