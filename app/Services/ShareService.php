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
     * Generic updating existing share
     * $data['amount] is a Money object from DebtService & ShareController
     */
    public function updateShare($data): Share
    {
        $share = Share::findOrFail($data['id']);

        // if we're not updating the amout, just put in the data
        // this is a temporary workaround
        // todo: actually do something with sent/seen
        if (!array_key_exists('amount', $data)) {
            
            $share->update($data);

            return $share;
        } else {
            
            $debt = $share->debt;
            $difference = $data['amount'];
            
            $share->amount = $share->amount->plus($difference);
            $share->save();

            $debt->amount = $debt->amount->plus($difference);
            $debt->save();

            if ($share->user_id != $share->debt->user_id) {
                $this->balanceService->updateGroupUserBalance($share, $difference);
            }

            return $share;
        }
    }

    /**
     * Specific to when shares are deleted during debt deletion
     */
    public function deleteDebtShares($data): void
    {
        foreach ($data as $share) {
            $share->delete();

            if ($share->user_id != $share->debt->user_id) {
                $this->balanceService->subtractFromGroupUserBalance($share);
            }
        }

        return; 
    }

    /**
     * For deleting a single share
     */
    public function deleteShare($data): void
    {
        // just delete the share
        $share = Share::findOrFail($data['id']);
        $share->delete();

        if ($share->user_id != $share->debt->user_id) {
            $this->balanceService->subtractFromGroupUserBalance($share);
        }

        // and adjust the debt amount
        $debt = $share->debt;
        $debt->amount = $debt->amount->minus($share->amount);
        $debt->save();

        return;
    }
}