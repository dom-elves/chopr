<?php

namespace App\Services;

use App\Models\Share;

class BalanceService

{
    public function addToGroupUserBalance($share): void
    {
        $share_owner = $share->user;
        $debt_owner = $share->debt->user;
        $share_group_user = $share_owner->group_users->where('user_id', $share->user_id)->first();
        $debt_group_user = $debt_owner->group_users->where('user_id', $debt_owner->id)->first();
        
        $share_group_user->balance += $share->amount;
        $share_group_user->save();

        if ($share_owner != $debt_owner) {
            $debt_group_user->balance -= $share->amount;
            $debt_group_user->save();
        } else {
            return;
        }
    }

    public function updateGroupUserBalance($share, $difference): void
    {
        $user = $share->user;
        $group_user = $user->group_users->where('user_id', $share->user_id)->first();
        $group_user->balance += $difference;
        $group_user->save();
    }

    public function subtractFromGroupUserBalance($share): void
    {
        $user = $share->user;
        $group_user = $user->group_users->where('user_id', $share->user_id)->first();
        $group_user->balance -= $share->amount;
        $group_user->save();
    }
}
 