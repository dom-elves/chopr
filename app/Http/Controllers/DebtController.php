<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\Group;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use App\Services\DebtService;
use Brick\Money\Money;
use App\Http\Resources\DebtResource;
use App\Http\Resources\GroupResource;
use App\Events\DebtCreated;
use App\Events\DebtUpdated;

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $debts = Inertia::scroll(fn() => 
            DebtResource::collection(
                // query builder to get the debts where the user is the owner
                // or has a share in the debt via group_user
                Debt::involved($user)
                    ->latest()
                    ->with([
                        'shares.groupUser.user:id,name',
                        'comments.groupUser.user:id,name',
                        'group.groupUsers.user',
                    ])
                    ->paginate(10)
            )
        );

        $groups = GroupResource::collection(
            $request->user()
                ->groups()
                ->with('groupUsers.user')
                ->get()
        );
            
        return Inertia::render('Debts', [
            'groups' => $groups,
            'debts' => $debts,
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
     * DebtCreated is the event which the listener listens for,
     * which then fires the notification.
     */
    public function store(StoreDebtRequest $request, DebtService $debtService): RedirectResponse
    {
        $validated = $request->validated();
        $group = Group::findOrFail($validated['group_id']);

        if ($request->user()->cannot('create', [Debt::class, $group])) {
            return redirect()->route('debt.index')->withErrors([
                'id' => "You do not have permission to create this debt."
            ]);
        }

        $debt = $debtService->createDebt($group, $validated);

        DebtCreated::dispatch($debt);

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
    public function update(UpdateDebtRequest $request, Debt $debt, DebtService $debtService): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()->cannot('update', $debt)) {
            return redirect()->route('debt.index')->withErrors([
                'id' => "You do not have permission to edit this debt."
            ]);
        }

        $debt = $debtService->updateDebt($debt, $validated);

        DebtUpdated::dispatch($debt);

        return redirect()->route('debt.index')->with('status', 'Debt updated successfully.');
    }
        
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Debt $debt, DebtService $debtService): RedirectResponse
    {
        if ($request->user()->cannot('delete', $debt)) {
            return redirect()->route('debt.index')->withErrors(['id' => "You do not have permission to delete this debt."]);
        } 

        $debtService->deleteDebt($debt);

        return redirect()
            ->route('debt.index')
            ->with('status', "Debt deleted successfully.");
    }
    
}
