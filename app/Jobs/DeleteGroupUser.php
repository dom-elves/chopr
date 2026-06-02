<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GroupUser;
use Carbon\Carbon;
use App\Jobs\DeleteDebt;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;

class DeleteGroupUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GroupUser $groupUser
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // this has to be reassigned as some weird thing happens with batachable and
        // serialisation where $this->groupUser is more than just a model instance
        $groupUser = $this->groupUser;

        $jobs = $groupUser->debts
            ->map(fn ($debt) => new DeleteDebt($debt))
            ->all();
   
        Bus::batch($jobs)
            ->then(fn () => $groupUser->delete())
            ->name('Delete ' . $groupUser->debts->count() . ' debts for group user ' . $groupUser->id)
            ->dispatch();
    }
}
