<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Brick\Money\Money;

class UpdateUserBalance implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $userId, 
        protected Money $amount
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('users')
            ->where('id', $this->userId)
            ->increment('balance', $this->amount->getMinorAmount()->toInt());
    }
}
