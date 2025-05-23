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
    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // add the debt
        $debt = Debt::create([
            'group_id' => $validated['group_id'],
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'split_even' => $validated['split_even'],
            'cleared' => 0,
            'currency' => $validated['currency'],
        ]);

        // for updating totals on the user that added the debt
        $user = Auth::user();

        // we don't rely on model events here
        // this could equally live in ShareController create() method
        // but since we're already doing extra bits here, it may as well live here
        foreach ($validated['user_shares'] as $share_data) {

            Model::withoutEvents(function() use ($share_data, $debt, $user) {
                $share = Share::create([
                    'debt_id' => $debt->id,
                    'user_id' => $share_data['user_id'],
                    'amount' => $share_data['amount'],
                    'name' => $share_data['name'],
                    'sent' => 0,
                    'seen' => 0,
                ]);

                // accordingly adjust the balance for the user adding the debt
                if ($debt->user_id === $share->user_id) {
                    $user->total_balance += $share_data['amount'];
                } else {
                    $user->total_balance -= $share_data['amount'];
                }
            });
        }

        return redirect()->route('dashboard')->with('status', 'Debt created successfully.');
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
    public function update(UpdateDebtRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $debt = Debt::findOrFail($validated['id']);

        // doesn't effect shares/balance so can do this regardless
        if ($debt->name !== $validated['name']) {
            $debt->update([
                'name' => $validated['name'],
            ]);
        }

        // different story if we're updating the amount
        if ($debt->amount != $validated['amount']) {
            // updating a debt that isn't split even will leave a discrepancy between
            // the debt amount and the shares total, this is handled by the frontend
            // the update is still allowed to happen, but the user gets a warning
            // that the totals do not add up
            $debt->update([
                'amount' => $validated['amount'],
            ]);

            if (!$debt->split_even) {

                $new = $debt->amount;
                $original = $debt->shares->sum('amount');
                $discrepancy = $new - $original;

                return redirect()->back()->withErrors([
                    'amount' => $discrepancy
                ]);
            }
            
            // if the debt is split even, calc the difference and new share amount
            if ($debt->split_even) {
                $rounded_split = floor(($debt->amount / $debt->shares->count()) * 100) / 100;

                foreach ($debt->shares as $share) {
                    $share->update([
                        'amount' => $rounded_split,
                    ]);
                }
            }
        }

        return redirect()->route('dashboard')->with('status', 'Debt updated successfully.');;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Debt $debt): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:debts,id', new IsDebtOwner],
        ])->validate();

        $debt = Debt::findOrFail($validated['id']);

        $debt->delete();

        return redirect()->route('dashboard')->with('status', 'Debt deleted successfully.');;
    }
}
