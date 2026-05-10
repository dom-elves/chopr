<?php

namespace App\Services;

use App\Models\Share;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Services\LedgerService;
use Brick\Money\Money;

class ShareService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * 'Create' methods
     */

    /**
     * Specific to when shares are created during debt creation.
     * 
     * @param Debt $debt
     * @param $user_shares_data array of share data from form request
     * @return void
     */
    public function createShares($debt, $user_shares_data): void
    {
        foreach ($user_shares_data as $share_data) {
            $this->createShare($debt, $share_data);
        }
    }

    /**
     * For the purpose of creating a single share for an existing debt.
     * If the debt is split even, we have to update other shares and keep the debt total,
     * then if it's not split, tack on new share amount to debt total.
     * 
     * @param Debt $debt
     * @param array $share_data
     * @return Share
     */
    public function createSingleShare($debt, $share_data): Share
    {
        $share = $this->createShare($debt, $share_data);
        
        if ($debt->split_even->value) {
            $this->updateShares($debt);
        } else {
            $debt->update([
                'amount' => $debt->amount->plus($share->amount),
            ]);
        }

        return $share;
    }

    /**
     * As both creating single & multiple shares require mostly the same logic,
     * keep the repeatable bits here.
     * 
     * @param Debt $debt
     * @param array $share_data
     * @return Share
     */
    private function createShare($debt, $share_data): Share
    {
        // todo: see if there's a better way to do this without query
        $share_user_id = GroupUser::findOrFail($share_data['group_user_id'])->user_id;

        $share = Share::create([
            'debt_id' => $debt->id,
            'group_user_id' => $share_data['group_user_id'],
            'name' => $share_data['name'],
            'amount' => $share_data['amount'],
            'sent' => $debt->groupUser->user->id === $share_user_id ? 1 : 0,
            'seen' => $debt->groupUser->user->id === $share_user_id ? 1 : 0,
        ]);

        $this->ledgerService->createShareLedgerEntry($share);

        return $share;
    }

    /**
     * 'Update' methods
     */

    /**
     * For the purpose of updating shares when a split even debt is updated,
     * when a regular debt is updated, shares are not edited and no ledger is required.
     * $data['name'] is passed through to save less hassle with updateShare(),
     * as that's used in so many places. Better to have a bit more logic there than,
     * have it all spread around.
     *
     * @param Debt $debt
     * @return void
     */
    public function updateShares($debt): void
    {
        $updated_splits = $debt->amount->split($debt->shares->count());
    
        foreach ($debt->shares as $key => $share) {
            $data['amount'] = $updated_splits[$key];
            $data['name'] = $share->name;
        
            $this->updateShare($share, $data);
        }
    }
    /**
     * For updating the name/amount of a single share.
     * As this can only be called when a single, standard debt share is updated,
     * manually adjust the debt amount here.
     *
     * @param Share $share
     * @param array $data
     * @return Share
     */
    public function updateSingleShare(Share $share, $data): Share 
    {
        $original_amount = $share->amount;
        $difference = Money::ofMinor($data['amount'], $share->debt->currency)->minus($original_amount);

        $share->debt->update([
                'amount' => $share->debt->amount->plus($difference),
            ]);

        $share = $this->updateShare($share, $data);

        return $share;
    }

    /**
     * Similar to creating shares, repeated logic can be done in one method.
     * If only the share name is being updated, no ledger entry is necessary.
     *
     * @param Share $share
     * @param array $data
     * @return Share
     */
    public function updateShare(Share $share, $data): Share
    {
        if ($share->name !== $data['name']) {
            $share = $this->updateShareName($share, $data['name']);
        };
  
        if ($share->amount->getMinorAmount()->toInt() !== $data['amount']) {
            $share = $this->updateShareAmount($share, $data['amount']);
        };

        return $share;
    }

    /**
     * For just updating the share name, no ledger entry required.
     * 
     * @param Share $share
     * @param string $name
     * @return Share
     */
    private function updateShareName(Share $share, string $name): Share
    {
        $share->update([
            'name' => $name,
        ]);

        return $share;
    }

    /**
     * For updating the share amount, needs ledger entry.
     * 
     * @param Share $share
     * @param int|Money $amount - because when updating a split even debt, 
     * shares will already be a Money, otherwise, an int direct from the frontend.
     * @return Share
     */
    private function updateShareAmount(Share $share, int|Money $amount): Share
    {
        if (!$amount instanceof Money) {
            $amount = Money::ofMinor($amount, $share->debt->currency);
        }

        $this->ledgerService->updateShareLedgerEntry($share, $amount);

        $share->update([
            'amount' => $amount,
        ]);

        return $share;
    }

    /**
     * As they are separate forms, sent & seen status are updated in their own methods.
     */

    /**
     * As we already have methods in create & delete for whole shares,
     * we don't need something like updateSeenLedgerEntry which will,
     * just have the same logic in as here. E.g. We're not 'deleting' a share,
     * as such, but we are removing it from a user's balance. The ledger doesn't,
     * care what the operation really just, just what is happening.
     * 
     * @param Share $share
     * @param bool $sent
     * @return Share
     */
    public function updateSentStatus(Share $share, $sent): Share
    {
        if ($sent) {
            $this->ledgerService->deleteShareLedgerEntry($share);
        } else {
            $this->ledgerService->createShareLedgerEntry($share);
        }

        $share->update([
            'sent' => $sent,
        ]);

        return $share;
    }

    /**
     * Completely cosmetic, function is just for the debt owner to communicate,
     * that they have received payment for a share.
     * 
     * @param Share $share
     * @param bool $seen
     * @return Share
     */
    public function updateSeenStatus(Share $share, $seen): Share
    {
        $share->update([
            'seen' => $seen,
        ]);

        return $share;
    }

    /**
     * 'Delete' methods,
     * deleteShares() is only called when deleting a split even debt,
     * the extra logic in deleteShare() for fixing totals and single shares was,
     * getting in the way. This is kinda just simpler.
     */

    /**
     * Delete shares when a debt is deleted.
     *
     * @param Debt $debt
     * @return void
     */
    public function deleteShares($debt): void
    {
        foreach ($debt->shares as $share) {
            $this->ledgerService->deleteShareLedgerEntry($share);
            $share->delete();
        }
    }

    /**
     * Delete a single share.
     *
     * If the debt is split, the debt total remains the same and shares are reclaced,
     * so we have to delete share first.
     *
     * If the debt is standard, deleting a share is taken off the amount of the debt,
     * then deleted.
     *
     * @param Share $share
     * @return void
     */
    public function deleteShare($share): void
    {
        $this->ledgerService->deleteShareLedgerEntry($share);

        if ($share->debt->split_even->value) {
            $share->delete();
            $this->updateShares($share->debt);
        } else {
            $share->debt->update([
                'amount' => $share->debt->amount->minus($share->amount),
            ]);
            $share->delete();
        }
    }
}