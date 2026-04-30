<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use Illuminate\Http\Request;
use App\Models\Share;
use App\Models\Debt;
use App\Services\ShareService;
use Illuminate\Http\RedirectResponse;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

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
     */
    public function store(StoreShareRequest $request, ShareService $shareService): RedirectResponse
    {
        $validated = $request->validated();
        $debt = Debt::findOrFail($validated['debt_id']);
        
        if ($request->user()->cannot('create', [Share::class, $debt])) {
            return redirect()->route('debt.index')->withErrors(['debt_id' => 'You do not have permission to add a share to this debt.']);
        }

        DB::transaction( function () use ($validated, $debt, $shareService) {
            return $shareService->createSingleShare($debt, $validated);
        });

        // eventually dispatch event, notif etc
        
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
        // switch case to handle share policy checks
        switch ($request->user()) {
            case $request->user()->cannot('update', $share):
                return redirect()
                    ->route('debt.index')
                    ->withErrors([
                        'share' => "You do not have permission to update this share."
                    ]);
            case $request->user()->cannot('updateName', $share):
                return redirect()
                    ->route('debt.index')
                    ->withErrors([
                        'name' => "You do not have permission to update the name of this share."
                    ]);
            case $request->user()->cannot('updateAmount', $share):
                return redirect()
                    ->route('debt.index')
                    ->withErrors([
                        'amount' => "You do not have permission to update the amount of this share."
                    ]);
            default:
                $validated = $request->validated();

                DB::transaction( function () use ($validated, $share, $shareService) { 
                    $shareService->updateSingleShare($share, $validated);
                });

                return redirect()
                    ->route('debt.index')
                    ->with('status', 'Share updated successfully.');
        }
    }

    /**
     * Update the 'sent' status of the specified resource in storage.
     */
    public function sent(UpdateShareRequest $request, Share $share, ShareService $shareService)
    {
        if ($request->user()->cannot('updateSent', $share)) {
            return redirect()
                ->route('debt.index')
                ->withErrors([
                    'sent' => "You do not have permission to update the 'sent' status of this share"
                ]);
        }

        $validated = $request->validated();

        DB::transaction( function () use ($share, $shareService, $validated) {
            $shareService->updateSentStatus($share, $validated['sent']);
        });

        return redirect()->route('debt.index');
    }

    /**
     * Update the 'seen' status of the specified resource in storage.
     */
    public function seen(UpdateShareRequest $request, Share $share, ShareService $shareService)
    {
        if ($request->user()->cannot('updateSeen', $share)) {
            return redirect()
                ->route('debt.index')
                ->withErrors([
                    'seen' => "You do not have permission to update the 'seen' status of this share"
                ]);
        }

        // if the user can update 'seen' and the share has not been sent
        // they cannot 'see' an 'unsent' share
        if ($share->sent == 0) {
            return redirect()
                ->route('debt.index')
                ->withErrors([
                    'seen' => "You can not mark this share as seen becase it has not been sent yet"
                ]);
        }
        
        $validated = $request->validated();

        DB::transaction( function () use ($share, $shareService, $validated) {
            $shareService->updateSeenStatus($share, $validated['seen']);
        });

        return redirect()->route('debt.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Share $share, ShareService $shareService): RedirectResponse
    {
        if ($request->user()->cannot('delete', $share)) {
            return redirect()->route('debt.index')
                ->withErrors([
                    'id' => 'You do not have permission to delete this share.'
                ]);
        }

        DB::transaction( function () use ($share, $shareService) {
            $shareService->deleteShare($share);
        });

        return redirect()
            ->route('debt.index')
            ->with('status', 'Share deleted successfully.');
    }
}
