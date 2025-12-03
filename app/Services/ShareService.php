<?php

namespace App\Services;

use App\Models\Share;
use App\Models\Debt;
use App\Services\BalanceService;
use Brick\Money\Money;

class ShareService
{
    protected BalanceService $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Specific to when shares are created during debt creation
     * @param $data array of share data from form request
     * @param Debt $debt
     * @return void
     */
    public function createDebtShares($data, $debt): void
    {
        // create shares
        foreach ($data as $share) {
            $share = Share::create([
                'debt_id' => $debt->id,
                'user_id' => $share['user_id'],
                'name' => $share['name'],
                'amount' => Money::of($share['amount'], $debt->currency),
                'sent' => $debt->user_id === $share['user_id'] ? 1 : 0,
                'seen' => $debt->user_id === $share['user_id'] ? 1 : 0,
            ]);

            $this->balanceService->addToGroupUserBalance($share);    
        }
        
        return;
    }

    /**
     * After a single share has been added, the debt & user balance must be updated
     * @param Share $share
     * @return void 
    */
    public function addToDebt($share): void
    {
        $debt = $share->debt;
 
        $debt->amount = $debt->amount->plus($share->amount);
        $debt->save();

        $this->balanceService->addToGroupUserBalance($share);

        return;
    }

    /**
     * After an individual share is updated in the controller, debt must be updated
     * And total balance recalced
     *
     * @param Share $share
     * @param Money $discrepancy
     * @return void
     */
    public function updateShareDebt($share, $discrepancy): void
    {
        $debt = $share->debt;
 
        $debt->amount = $debt->amount->plus($discrepancy);
        $debt->save();

        $this->balanceService->updateGroupUserBalance($share, $discrepancy);    
      
        return;
    }


    /**
     * Update shares equally, currerntly only used as a part of updating
     * a split even debt
     *
     * @param Debt $debt
     * @param Money $discrepancy
     * @return void
     */
    public function updateDebtShares($debt, $discrepancy): void
    {
        // the discrepancy between new and old debt amount, 
        // split between the amount of shares
        $discrepancy_shares = $discrepancy->split($debt->shares->count());

        foreach ($debt->shares as $index => $share) {
            $difference = $discrepancy_shares[$index];

            $share->amount = $share->amount->plus($difference);
            $share->save();

            $this->balanceService->updateGroupUserBalance($share, $difference);    
        }

        return;
    }

    /**
     * Specific to when shares are deleted during debt deletion
     * 
     * @param Debt $debt
     * @return void
     */
    public function deleteDebtShares($debt): void
    {
        foreach ($debt->shares as $share) {
            $this->balanceService->subtractFromGroupUserBalance($share, $share->amount);

            $share->delete();
        }

        return; 
    }

    /**
     * I know the naming doesn't actually make sense but i'm just following my convention
     * This is for updating debt & balance after deleting a single share
     * 
     * @param Share $share
     * @return void
     */
    public function subtractFromDebt($share): void
    {
        $this->balanceService->subtractFromGroupUserBalance($share, $share->amount);

        // and adjust the debt amount
        $debt = $share->debt;
        $debt->amount = $debt->amount->minus($share->amount);
        $debt->save();

        return;
    }
}