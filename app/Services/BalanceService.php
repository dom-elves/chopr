<?php

namespace App\Services;

use App\Models\Share;

class BalanceService

{
    public function calcUserBalance($share): void
    {
        $user = $share->user;
        $debt_owner = $share->debt->user;

        if($share->isDirty()) {
            dd('stop');
        }

        if ($share->user_id !== $debt_owner->id) {
            $user->total_balance -= $share->amount;
            $user->save();

            $debt_owner->total_balance += $share->amount;
            $debt_owner->save();
        } 
    }
}
 