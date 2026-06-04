<?php

namespace App\Jobs\Ledger;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Share;
use Illuminate\Support\Facades\Bus;
use App\Enums\LedgerEntryType;
use App\Models\LedgerEntry;

class DeleteShareLedgerEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Share $share,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $share = $this->share;

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->debt->groupUser->user->id,
            'amount' => $share->amount->negated(),
            'type' => LedgerEntryType::DEBT_OWNERSHIP_DELETED,
        ]);

        // $this->updateUserBalance($share->debt->groupUser->user->id, $share->amount->negated());

        LedgerEntry::create([
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'amount' => $share->amount,
            'type' => LedgerEntryType::SHARE_DELETED,
        ]);

        // $this->updateUserBalance($share->groupUser->user->id, $share->amount);
    }
}
