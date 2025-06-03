<?php

namespace App\Services;

use App\Models\Share;

class BalanceService

{
    public function addToGroupUserBalance($share): void
    {
        [$share_group_user, $debt_group_user] = $this->getGroupUsers($share);

        $share_group_user->balance += $share->amount;
        $share_group_user->save();

        if ($share_group_user != $debt_group_user) {
            $debt_group_user->balance -= $share->amount;
            $debt_group_user->save();
        } else {
            return;
        }
    }

    public function updateGroupUserBalance($share, $difference): void
    {
        [$share_group_user, $debt_group_user] = $this->getGroupUsers($share);
        
        $share_group_user->balance += $difference;
        $share_group_user->save();

        if ($share_group_user != $debt_group_user) {
            $debt_group_user->balance -= $difference;
            $debt_group_user->save();
        } else {
            return;
        }
    }

    public function subtractFromGroupUserBalance($share): void
    {
        [$share_group_user, $debt_group_user] = $this->getGroupUsers($share);

        $share_group_user->balance -= $share->amount;
        $share_group_user->save();

        if ($share_group_user != $debt_group_user) {
            $debt_group_user->balance += $share->amount;
            $debt_group_user->save();
        } else {
            return;
        }
    }

    private function getGroupUsers(Share $share)
    {
        $share_owner = $share->user;
        $debt_owner = $share->debt->user;
        $share_group_user = $share_owner->group_users->where('user_id', $share->user_id)->first();
        $debt_group_user = $debt_owner->group_users->where('user_id', $debt_owner->id)->first();

        return [$share_group_user, $debt_group_user];
    }
}
 