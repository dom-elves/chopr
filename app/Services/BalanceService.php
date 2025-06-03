<?php

namespace App\Services;

use App\Models\Share;

class BalanceService

{
    public function addToGroupUserBalance($share): void
    {
        $user = $share->user;
        $group_user = $user->group_users->where('user_id', $share->user_id)->first();
        $group_user->balance += $share->amount;
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
 