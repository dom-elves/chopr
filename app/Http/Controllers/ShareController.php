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
        if ($request->user()->cannot('create',  [Share::class, $request->get('debt_id')] )) {
            return redirect()->route('debt.index')->withErrors(['debt_id' => 'You do not have permission to add a share to this debt.']);
        }

        $validated = $request->validated();

        $debt = Debt::findOrFail($validated['debt_id']);

        $share = Share::create([
            'debt_id' => $validated['debt_id'],
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'amount' => Money::of($validated['amount'], $validated['currency']),
            'sent' => $debt->user_id === $validated['user_id'] ? 1 : 0,
            'seen' => $debt->user_id === $validated['user_id'] ? 1 : 0,
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
        
        // switch case to handle share policy checks
        switch ($request->user()) {
            case $request->user()->cannot('updateName', $share) && $request->user()->cannot('updateAmount', $share):
                 return redirect()->route('debt.index')->withErrors(['share' => "You do not have permission to update this share."]);
            case $request->user()->cannot('updateName', $share):
                 return redirect()->route('debt.index')->withErrors(['name' => "You do not have permission to update the name of this share."]);
            case $share->wasChanged('amount') && $request->user()->cannot('updateAmount', $share):
                 return redirect()->route('debt.index')->withErrors(['amount' => "You do not have permission to update the amount of this share."]);
            default:
                $original_amount = $share->amount;
        
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
    }

    /**
     * Update the 'sent' status of the specified resource in storage.
     */
    public function sent(UpdateShareRequest $request, Share $share, BalanceService $balanceService)
    {
        if ($request->user()->can('updateSent', $share)) {
            $validated = $request->validated();

            $share->update([
                'sent' => $validated['sent'],
            ]);

            // only change user balances on sent status update
            // 'seen' is merely cosmetic, jsut for user clarity
            // maybe one day can expand balance into having a pending/unconfirmed status
            if ($validated['sent'] == 1) {
                $balanceService->subtractFromGroupUserBalance($share, $share->amount);
            } else {
                $balanceService->addToGroupUserBalance($share, $share->amount);
            }
            
            return redirect()->route('debt.index');
        } 

        return redirect()->route('debt.index')->withErrors(['sent' => "You do not have permission to update the 'sent' status of this share"]);
    }

    /**
     * Update the 'seen' status of the specified resource in storage.
     */
    public function seen(UpdateShareRequest $request, Share $share)
    {
        if ($request->user()->can('updateSeen', $share)) {
            if ($share->sent == 0) {
                return redirect()->route('debt.index')->withErrors(['seen' => "You can not mark this share as seen becase it has not been sent yet"]);
            } else {
                $validated = $request->validated();

                $share->update([
                    'seen' => $validated['seen'],
                ]);

                return redirect()->route('debt.index');
            }
        } 

        return redirect()->route('debt.index')->withErrors(['seen' => "You do not have permission to update the 'seen' status of this share"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Share $share, ShareService $shareService): RedirectResponse
    {
        if ($request->user()->cannot('delete', $share)) {
            return redirect()->route('debt.index')->withErrors(['id' => 'You do not have permission to delete this share.']);
        }

        // delete the share
        $share->delete();
        
        // mentioned in docblock, function name makes no sense
        // but it's for updating debt & user balance
        $shareService->subtractFromDebt($share);

        return redirect()
            ->route('debt.index')
            ->with('status', 'Share deleted successfully.');
    }
}
