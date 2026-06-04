<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Share;
use App\Jobs\Ledger\DeleteShareLedgerEntry;
use Illuminate\Support\Facades\Bus;

class DeleteShare implements ShouldQueue
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
        $share->delete();
    }
}
