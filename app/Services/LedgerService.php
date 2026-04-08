<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Share;
use Illuminate\Support\Facades\DB;

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

        $this->updateUserBalance($share->debt->groupUser->user->id, $share->amount);

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount' => - $share->amount,
            'type' => 'share_deducted',
        ]);

        $this->updateUserBalance($share->groupUser->user->id, - $share->amount);
    }

    public function updatedLedgerEntry(Share $share, int $new_amount): void
    {
        $original_amount = $share->amount;
        $difference = $new_amount - $original_amount;

        if (!$difference) {
            $difference = $share->amount;
        }

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount'  => $difference,
            'type'    => 'debt_ownership_update',
        ]);

        $this->updateUserBalance($share->debt->groupUser->user->id, $difference);

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount'  => - $difference,
            'type'    => 'share_update',
        ]);

        $this->updateUserBalance($share->groupUser->user->id, - $difference);
    }

    public function deleteLedgerEntry($share): void
    {
        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount' => - $share->amount,
            'type' => 'debt_ownership',
        ]);

        $this->updateUserBalance($share->debt->groupUser->user->id, - $share->amount);

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount' => $share->amount,
            'type' => 'share_deducted',
        ]);

        $this->updateUserBalance($share->groupUser->user->id, $share->amount);
    }

    private function updateUserBalance(int $user_id, int $amount): void
    {
        DB::table('users')
            ->where('id', $user_id)
            ->increment('balance', $amount);
    }
}