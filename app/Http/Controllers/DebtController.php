<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\GroupUser;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
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
        dump('test');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDebtRequest $request)
    {
        $user = Auth::user();
        $group_user = GroupUser::where('group_id', $request->group_id)
            ->where('user_id', $user->id)
            ->first();

        $request->validated();
        dd($request);
        Debt::create([
            'group_id' => $request->group_id,
            'collector_group_user_id' => $group_user->id,
            'name' => $request->debt_name,
            'amount' => $request->amount,
            'split_even' => $request->split_even,
            'cleared' => 0,
        ]);
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
    public function update(UpdateDebtRequest $request, Debt $debt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Debt $debt)
    {
        Debt::where('id', $request->debt_id)->delete();
    }
}
