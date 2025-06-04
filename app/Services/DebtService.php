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

    /**
     * Create a new debt.
     *
     * @param array $data
     * @return Debt|mixed
     */
    public function createDebt($data): Debt 
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
        $this->shareService->createDebtShares($data['user_shares'], $debt);

        return $debt;
    }

    /**
     * Update an existing debt.
     *
     * @param array $data
     * @return Debt|mixed
     */
    public function updateDebt($data): mixed
    {
        $debt = Debt::findOrFail($data['id']);

        $debt->update($data);

        // we don't call any sort of share update
        // as the frontend shows an error based around discrepancy
        // it's down to the user to update shares to fix this

        return $debt;
    }

    public function deleteDebt($data): void
    {
        // find the debt
        $debt = Debt::findOrFail($data['id']);

        // delete the shares
        $this->shareService->deleteDebtShares($debt->shares);

        // and finally the debt
        $debt->delete();

        return;
    }
}