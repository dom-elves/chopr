<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ShareUpdated;

class UpdateUserTotalBalance
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShareUpdated $event): void
    {
        dd($event);
        // $amount = $event->share->amount;
        // $user = $event->share->debt->user;

        // $user->total_balance += $amount;
        // $user->save();

        // 
    }
}
