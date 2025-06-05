<?php

namespace App\Services;

use App\Models\Share;

class BalanceService

{
    /**
     * Note to explain how this works, because I keep forgetting myself:
     * A positive balance means you are 'in credit', i.e. owed money
     * A negative balance means you owe money across x debts, you are currently at a net negative    * 
     */
    public function addToGroupUserBalance($share): void
    {
        [$share_group_user, $debt_group_user] = $this->getGroupUsers($share);

        $share->group_user->balance -= $share->amount;
        $share->group_user->save();

        $debt_group_user->balance += $share->amount;
        $debt_group_user->save();
    
    }

    public function updateGroupUserBalance($share, $difference): void
    {
        [$share_group_user, $debt_group_user] = $this->getGroupUsers($share);
        
        $share_group_user->balance -= $difference;
        $share_group_user->save();

        $debt_group_user->balance += $difference;
        $debt_group_user->save();
       
    }

    public function subtractFromGroupUserBalance($share): void
    {
        [$share_group_user, $debt_group_user] = $this->getGroupUsers($share);

        $share_group_user->balance += $share->amount;
        $share_group_user->save();

        $debt_group_user->balance -= $share->amount;
        $debt_group_user->save();
      
    }

    private function getGroupUsers(Share $share)
    {
        $share_group_user = $share->group_user;
        $debt_group_user = $share->debt->user->group_users
            ->where('group_id', $share->debt->group_id)
            ->first();

        return [$share_group_user, $debt_group_user];
    }
}
 