<?php

namespace App\Services;

use App\Models\Share;
use App\Models\GroupUser;

class BalanceService

{
    /**
     * Note to explain how this works, because I keep forgetting myself:
     * A positive balance means you are 'in credit', i.e. owed money
     * A negative balance means you owe money across x debts, you are currently at a net negative    * 
     */
    public function addToGroupUserBalance($share): void
    {
        if ($share->groupUser->id === $share->debt->group_user_id) {
            return;
        } else {
            $debt_owner = $share->debt->groupUser;
        
            $debt_owner->balance = $debt_owner->balance->plus($share->amount);
            $debt_owner->save();

            $share->groupUser->balance = $share->groupUser->balance->minus($share->amount);
            $share->groupUser->save();
        }
    }

    /**
     * Update the group user balance based on the change in value of a single share
     * We call the method on any share where amount is updated
     * but only actually update balances when it's not the owner being updated
     *
     * @param Share $share
     * @param Money $difference
     * @return void
     */
    public function updateGroupUserBalance($share, $difference): void
    {
        if ($share->groupUser->id === $share->debt->group_user_id) {
            return;
        } else {
            $debt_owner = $share->groupUser;

            $debt_owner->balance = $debt_owner->balance->plus($difference);
            $debt_owner->save();

            $share->groupUser->balance = $share->groupUser->balance->minus($difference);
            $share->groupUser->save();
        }
    }

    /**
     * Subtract debt shares from user balance, used in delete process
     * Basically same code from updateGroupUserBalance, just inverted operations
     * 
     * @param Share $share
     * @param Money $difference
     * @return void
     */
    public function subtractFromGroupUserBalance($share, $difference): void
    {
        if ($share->groupUser->id === $share->debt->group_user_id) {
            return;
        } else {
            $debt_owner = $share->groupUser;

            $debt_owner->balance = $debt_owner->balance->minus($difference);
            $debt_owner->save();

            $share->groupUser->balance = $share->groupUser->balance->plus($difference);
            $share->groupUser->save();
        }
    }
}
 