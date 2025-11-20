<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Http\Requests\SendShareRequest;
use Illuminate\Http\Request;
use App\Models\Share;
use App\Models\Debt;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsShareOwner;
use App\Rules\IsShareDebtOwner;
use App\Events\ShareUpdated;
use App\Services\ShareService;
use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Brick\Money\Money;

/**
 * Shares have observers, which fire events that perform operations for debt & user->total_balance
 */
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
     * 
     */
    public function store(StoreShareRequest $request, ShareService $shareService): RedirectResponse
    {
        $validated = $request->validated();

        $share = Share::create([
            'debt_id' => $validated['debt_id'],
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'amount' => Money::of($validated['amount'], $validated['currency']),
            'sent' => 0,
            'seen' => 0,
        ]);

        $shareService->addToDebt($share);
        
        return redirect()->route('debt.index')->with('status', 'Share created successfully.');
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
    public function update(UpdateShareRequest $request, Share $share, ShareService $shareService): RedirectResponse
    {
        // validated data
        $validated = $request->validated();

        $original_amount = $share->amount;

        // update data
        $share->update([
            'name' => $validated['name'],
            'amount' => Money::of($validated['amount'], $share->debt->currency),
        ]);

        // similar to updating a debt, extra stuff to do if a share amount is updated
        if ($share->wasChanged('amount')) {
            $discrepancy = $share->amount->minus($original_amount);
            $shareService->updateShareDebt($share, $discrepancy);
        }

        return redirect()->route('debt.index')->with('status', 'Share updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Share $share, ShareService $shareService): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:shares,id'],
            // could use IsShareDebtOwner but the wording on $fail is totally different
            'debt_id' => ['required', 'integer', 'exists:debts,id', function($attribute, $value, $fail) use ($share) {
                if ($share->debt->user_id !== Auth::user()->id) {
                    $fail('You do not have permission to delete this share');
                }
            }],
        ])->validate();

        // delete the share
        $share->delete();
        
        // mentioned in docblock, function name makes no sense
        // but it's for updating debt & user balance
        $shareService->subtractFromDebt($share);

        return redirect()->route('debt.index')->with('status', 'Share deleted successfully.');
    }
}
