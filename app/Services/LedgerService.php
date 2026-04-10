<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Share;
use Illuminate\Support\Facades\DB;
use Brick\Money\Money;

class LedgerService
{
    /**
     * When a share is created in any capacity, create ledgers for debt owner
     * and for the share owner.
     * 
     * @param Share $share
     * @return void
     */
    public function createLedgerEntry(Share $share): void
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
            'amount' => $share->amount->negated(),
            'type' => 'share_deducted',
        ]);

        $this->updateUserBalance($share->groupUser->user->id, $share->amount->negated());
    }

    /**
     * Same concept as creation, but need to calc the difference first.
     * 
     * @param Share $share
     * @param Money $new_amount
     * @return void
     */
    public function updatedLedgerEntry(Share $share, Money $new_amount): void
    {
        $original_amount = $share->amount;
        $difference = $new_amount->minus($original_amount);

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
            'amount'  => $difference->negated(),
            'type'    => 'share_update',
        ]);

        $this->updateUserBalance($share->groupUser->user->id, $difference->negated());
    }

    /**
     * Deletion is just the inverse of creation.
     * 
     * @param Share $share
     * @return void
     */
    public function deleteLedgerEntry(Share $share): void
    {
        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount' => $share->amount->negated(),
            'type' => 'debt_ownership',
        ]);

        $this->updateUserBalance($share->debt->groupUser->user->id, $share->amount->negated());

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount' => $share->amount,
            'type' => 'share_deducted',
        ]);

        $this->updateUserBalance($share->groupUser->user->id, $share->amount);
    }

    /**
     * increment() does += for the user. DB::table() is just quicker than User::where() etc.
     *
     * @param int $user_id
     * @param Money $amount
     * @return void
     */
    private function updateUserBalance(int $user_id, Money $amount): void
    {
        DB::table('users')
            ->where('id', $user_id)
            ->increment('balance', $amount->getMinorAmount()->toInt());
    }
}