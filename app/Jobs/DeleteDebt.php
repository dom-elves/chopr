<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Debt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use App\Jobs\DeleteShare;

class DeleteDebt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Debt $debt,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $debt = $this->debt;

        $jobs = $debt->shares
            ->map(fn ($share) => new DeleteShare($share))
            ->all();

        Bus::batch($jobs)
            ->then(fn () => $debt->delete())
            ->name('Delete ' . $debt->shares->count() . ' shares for debt ' . $debt->id)
            ->dispatch();
    }
}
