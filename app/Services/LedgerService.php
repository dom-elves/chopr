<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Share;
use Illuminate\Support\Facades\DB;
use Brick\Money\Money;
use App\Enums\LedgerEntryType;
use App\Jobs\UpdateUserBalance;

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
     * Fire ledger updates in a job as this is the most used process.
     *
     * @param int $userId
     * @param Money $amount
     * @return void
     */
    private function updateUserBalance(int $userId, Money $amount): void
    {
        UpdateUserBalance::dispatch($userId, $amount);
    }
}