<?php

namespace App\Services;

use App\Models\Debt;
use App\Services\ShareService;

class DebtService
{
    protected ShareService $shareService;

    public function __construct(ShareService $shareService)
    {
        $this->shareService = $shareService;
    }

    public function createDebt($data) 
    {
        // create the debt with validated data
        $debt = Debt::create([
            'group_id' => $data['group_id'],
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'amount' => $data['amount'],
            'split_even' => $data['split_even'],
            'cleared' => 0,
            'currency' => $data['currency'],
        ]);

        // create the relative shares
        $this->shareService->createInitialShares($data['user_shares'], $debt);

        // manage balances (maybe in another service?)
        
        return $debt;
    }

    public function updateDebt()
    {

    }

    public function deleteDebt()
    {

    }
}