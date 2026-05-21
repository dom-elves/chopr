<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Share;
use Illuminate\Support\Facades\DB;
use Brick\Money\Money;
use App\Enums\LedgerEntryType;

class LedgerService
{
    /**
     * When a share is created in any capacity, create ledgers for debt owner
     * and for the share owner.
     * 
     * @param Share $share
     * @return void
     */
    public function createShareLedgerEntry(Share $share): void
    {
        DB::transaction( function () use ($share) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $share->debt->groupUser->user->id,
                'amount' => $share->amount,
                'type' => LedgerEntryType::DEBT_OWNERSHIP_CREATED,
            ]);
        });

        $this->updateUserBalance($share->debt->groupUser->user->id, $share->amount);

        DB::transaction( function () use ($share) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $share->groupUser->user->id,
                'amount' => $share->amount->negated(),
                'type' => LedgerEntryType::SHARE_CREATED,
            ]);
        });

        $this->updateUserBalance($share->groupUser->user->id, $share->amount->negated());
    }

    /**
     * Same concept as creation, but need to calc the difference first.
     * 
     * @param Share $share
     * @return void
     */
    public function updateShareLedgerEntry(Share $share): void
    {
        $original_amount = $share->getOriginal('amount');
        $difference = $share->amount->minus($original_amount);

        if (!$difference) {
            $difference = $share->amount;
        }

        DB::transaction( function () use ($share, $difference) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $share->debt->groupUser->user->id,
                'amount'  => $difference,
                'type'    => LedgerEntryType::DEBT_OWNERSHIP_UPDATED,
            ]);
        });

        $this->updateUserBalance($share->debt->groupUser->user->id, $difference);

        DB::transaction( function () use ($share, $difference) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $share->groupUser->user->id,
                'amount'  => $difference->negated(),
                'type'    => LedgerEntryType::SHARE_UPDATED,
            ]);
        });

        $this->updateUserBalance($share->groupUser->user->id, $difference->negated());
    }

    /**
     * Deletion is just the inverse of creation.
     * 
     * @param Share $share
     * @return void
     */
    public function deleteShareLedgerEntry(Share $share): void
    {
        dump($share);
        DB::transaction( function () use ($share) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $share->debt->groupUser->user->id,
                'amount' => $share->amount->negated(),
                'type' => LedgerEntryType::DEBT_OWNERSHIP_DELETED,
            ]);
        });

        $this->updateUserBalance($share->debt->groupUser->user->id, $share->amount->negated());

        DB::transaction( function () use ($share) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $share->groupUser->user->id,
                'amount' => $share->amount,
                'type' => LedgerEntryType::SHARE_DELETED,
            ]);
        });

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
        DB::transaction( function () use ($user_id, $amount) {
            DB::table('users')
                ->where('id', $user_id)
                ->increment('balance', $amount->getMinorAmount()->toInt());
        });
    }
}