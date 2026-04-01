<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Share;

class LedgerService
{
    public function createLedgerEntry($share): void
    {
        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount' => $share->amount,
            'type' => 'debt_ownership',
        ]);

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount' => - $share->amount,
            'type' => 'share_deducted',
        ]);
    }

    public function updatedLedgerEntry(Share $share, int $new_amount): void
    {
        $original_amount = $share->amount;
        $difference = $new_amount - $original_amount;

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount'  => -$difference,
            'type'    => 'debt_ownership_update',
        ]);

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount'  => $difference,
            'type'    => 'share_update',
        ]);
    }
}