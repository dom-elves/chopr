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
                'sent' => 0,
                'seen' => 0,
            ]);

            if ($share->user_id != $share->debt->user_id) {
                $this->balanceService->addToGroupUserBalance($share);
            }
        }
        
        return;
    }

    /**
     * For adding a single share
     */
    public function createShare($data): Share
    {
        $debt = Debt::findOrFail($data['debt_id']);

        // create the share
        $share = Share::create([
            'debt_id' => $data['debt_id'],
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'amount' => Money::of($data['amount'], $debt->currency),
            'sent' => 0,
            'seen' => 0,
        ]);

        if ($share->user_id != $share->debt->user_id) {
            $this->balanceService->addToGroupUserBalance($share);
        }
        
        // update debt amount
        $debt->amount = $debt->amount->plus($share->amount);
        $debt->save();

        return $share;
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
    public function deleteShareDebt($share): void
    {
        $this->balanceService->subtractFromGroupUserBalance($share, $share->amount);

        // and adjust the debt amount
        $debt = $share->debt;
        $debt->amount = $debt->amount->minus($share->amount);
        $debt->save();

        return;
    }
}