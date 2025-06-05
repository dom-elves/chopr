<?php

namespace App\Services;

use App\Models\Share;
use App\Services\BalanceService;

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
                'amount' => $share['amount'],
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
        // create the share
        $share = Share::create([
            'debt_id' => $data['debt_id'],
            'user_id' => $data['user_id'],
            // todo: add name when designing form
            // 'name' => $data['name'],
            'amount' => $data['amount'],
            'sent' => 0,
            'seen' => 0,
        ]);

        if ($share->user_id != $share->debt->user_id) {
            $this->balanceService->addToGroupUserBalance($share);
        }

        // update debt amount
        $debt = $share->debt;
        $debt->amount += $data['amount'];
        $debt->save();

        return $share;
    }

    /**
     * Generic updating existing share
     */
    public function updateShare($data): Share
    {
        // update share with new amount, get original too
        $share = Share::findOrFail($data['id']);
        $old = $share->amount;
        $new = $data['amount'];
        $difference = $new - $old;
        $share->update($data);

        if ($share->user_id != $share->debt->user_id) {
            $this->balanceService->updateGroupUserBalance($share, $difference);
        }

        // adjust the debt amount by new minus old, using +=
        $debt = $share->debt;
        $debt->amount += $difference;
        $debt->save();
      
        return $share;
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
        $debt->amount -= $share->amount;
        $debt->save();

        return;
    }
}