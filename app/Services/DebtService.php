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
     * not when a share being updated then updates a debt.
     * @param Debt $debt
     * @param array $data
     * @return Debt
     */
    public function updateDebt($debt, $data): Debt
    {
        return DB::transaction(function () use ($debt, $data) {
            $debt->update([
                'name' => $data['name'],
                'amount' => $data['amount'],
            ]);

            if ($debt->split_even) {
                $this->shareService->updateShares($debt);
            }

            return $debt;
        });
    }
}