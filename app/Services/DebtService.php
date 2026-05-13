<?php

namespace App\Services;

use App\Models\Debt;
use App\Services\ShareService;
use Illuminate\Support\Facades\DB;

/**
 * Debt service layer is practially just cosmetic,
 * most logic around who owes what etc. is in the ShareService,
 * as that's where all logic around Ledgers will live.
 */
class DebtService
{
    protected ShareService $shareService;

    public function __construct(ShareService $shareService)
    {
        $this->shareService = $shareService;
    }

    /**
     * For the purpose of creating a single debt with shares, the more complex logic of share creation during debt creation is handled in the service layer to avoid bloating the controller. The same applies for updating a debt with shares.
     * 
     * @param Group $group
     * @param array $data
     * @return Debt
     */
    public function createDebt($group, $data): Debt
    {
        return DB::transaction(function () use ($data, $group) {
            $debt = Debt::create([
                'group_id' => $group->id,
                'group_user_id' => $data['group_user_id'],
                'name' => $data['name'],
                'amount' => $data['amount'],
                'split_even' => $data['split_even'],
                'cleared' => 0,
                'currency' => $data['currency'],
            ]);

            $this->shareService->createShares($debt, $data['user_shares']);

            return $debt;
        });
    }

    /**
     * For the purpose of updating a debt,
     * only called when the debt itself is being directly updated,
     * 
     * not when a share being updated then updates a debt.
     * @param Debt $debt
     * @param array $data
     * @return Debt
     */
    public function updateDebt($debt, $data): Debt
    {
        return DB::transaction(function () use ($debt, $data) {
            if ($debt->name !== $data['name']) {
                $debt = $this->updateDebtName($debt, $data['name']);
            }

            if ($debt->amount->getMinorAmount()->toInt() !== $data['amount']) {
                $debt = $this->updateDebtAmount($debt, $data['amount']);
            }

            return $debt;
        });
    }

    /**
     * For just updating the debt name.
     * 
     * @param Debt $debt
     * @param string $name
     * @return Debt
     */
    public function updateDebtName($debt, $name): Debt
    {
        return DB::transaction(function () use ($debt, $name) {
            $debt->update([
                'name' => $name,
            ]);

            return $debt;
        });
    }

    /**
     * For updating the debt amount.
     * If it's a split debt, shares are recalculated after the amount has been set.
     * Otherwise, the amont just gets updated and the frontend shows a discrepancy.
     * 
     * @param Debt $debt
     * @param int $amount
     * @return Debt
     */
    public function updateDebtAmount($debt, $amount): Debt
    {
        return DB::transaction(function () use ($debt, $amount) {
            $debt->update([
                'amount' => $amount,
            ]);

            if ($debt->split_even->value) {
                $this->shareService->updateShares($debt);
            }

            return $debt;
        });
    }

    /**
     * Delete the debt & the related shares
     * @param Debt $debt
     * @return void
     */
    public function deleteDebt($debt): mixed
    {
        return DB::transaction(function () use ($debt) {
            $this->shareService->deleteShares($debt);
            $debt->delete();
        });
    }
}