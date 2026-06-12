<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LedgerEntry;
use App\Models\Share;
use App\Enums\LedgerEntryType;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Services\ShareService;

class DeleteShare implements ShouldQueue
{
    use Queueable, Batchable, Dispatchable;

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
        $debt = $this->share->debt;
        $debtor = $this->share->debt->groupUser->user;

        DB::transaction( function() use ($debtor, $share) {
            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $debtor->id,
                'amount' => $share->amount->negated(),
                'type' => LedgerEntryType::DEBT_OWNERSHIP_DELETED,
            ]);

            DB::table('users')
                ->where('id', $debtor->id)
                ->increment('balance', $share->amount->negated()->getMinorAmount()->toInt());

            $indebted = $share->groupUser->user;

            LedgerEntry::create([
                'share_id' => $share->id,
                'user_id' => $indebted->id,
                'amount' => $share->amount,
                'type' => LedgerEntryType::SHARE_DELETED,
            ]);

            DB::table('users')
                ->where('id', $indebted->id)
                ->increment('balance', $share->amount->getMinorAmount()->toInt());

            $share->delete();
        });

        if ($debt->split_even && $debt->shares->count() === 1) {
            $shareService = new ShareService();
            $shareService->updateShares($debt);
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->share->id)];
    }
}
