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

        // relationships for debts
        $relationships = [
            'shares.group_user.user',
            'comments.user',
            'group.group_users.user',
        ];

        // debts owned
        $debts = $request->user()->debts()
            ->with($relationships)
            ->get()
            ->merge(
            // debts involved in (not owner, but has share)
            $request->user()->involvedDebts()
                ->with($relationships)
                ->get()
            );

        // just groups
        $groups = $request->user()
            ->groups()
            ->with('group_users.user')
            ->get();

        return Inertia::render('Debts', [
            'groups' => $groups,
            'debts' => $debts->sortByDesc('created_at')->values(),
            'status' => $request->session()->get('status') ?? null,
        ]);
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
    public function store(StoreDebtRequest $request, DebtService $debtService): RedirectResponse
    {
        $validated = $request->validated();
    
        $debtService->createDebt($validated);

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
    public function update(UpdateDebtRequest $request, DebtService $debtService): RedirectResponse
    {
        $validated = $request->validated();
        $original_amount = Debt::findOrFail($validated['id'])->amount;
        $updated = $debtService->updateDebt($validated);
        
        // as mentioned in DebtService, discrepancy handling
        if ($original_amount != $updated->amount && !$updated->split_even) {
            $discrepancy = $updated->amount->minus($original_amount)->getAmount()->toInt();
            
            return redirect()->route('debt.index')->withErrors([
                'amount' => $discrepancy
            ]);

        } else {
            return redirect()->route('debt.index')->with('status', 'Debt updated successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DebtService $debtService): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:debts,id', new IsDebtOwner],
        ])->validate();
 
        $debtService->deleteDebt($validated);

        return redirect()->route('debt.index')->with('status', 'Debt deleted successfully.');;
    }
}
