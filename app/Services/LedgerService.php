<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Share;

class LedgerService
{
    public function createLedgerEntry($share): void
    {
        dump($share);
        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount'  => $share->amount,
            'type'    => 'debt_ownership',
        ]);

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount'  => $share->amount,
            'type'    => 'share_deducted',
        ]);
    }
}