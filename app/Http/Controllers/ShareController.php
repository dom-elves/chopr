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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

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

        $shareService->createShare($validated);
        
        return redirect()->route('dashboard')->with('status', 'Share created successfully.');
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
    public function update(UpdateShareRequest $request, ShareService $shareService): RedirectResponse
    {
        // validated data
        $validated = $request->validated();

        $shareService->updateShare($validated);

        return redirect()->route('dashboard')->with('status', 'Share updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ShareService $shareService): RedirectResponse
    {
  
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:shares,id'],
            // could use IsShareDebtOwner but the wording on $fail is totally different
            'debt_id' => ['required', 'integer', 'exists:debts,id', function($attribute, $value, $fail) {
                $debt = Debt::findOrFail($value);
                if ($debt->user_id !== Auth::user()->id) {
                    $fail('You do not have permission to delete this share');
                }
            }],
        ])->validate();
           
        $shareService->deleteShare($validated);

        return redirect()->route('dashboard')->with('status', 'Share deleted successfully.');
    }
}
