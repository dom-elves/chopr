<?php

namespace App\Listeners;

use App\Events\DebtCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\DebtCreatedNotification;

class DebtCreatedListener implements ShouldQueue
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
    public function handle(DebtCreated $event): void
    {
        foreach ($event->debt->shares as $share) {
            // automatically broadcasts on the user channel in channels.php
            // otherwise, you broadcast it in the event
            $share->groupUser->user->notify(new DebtCreatedNotification($event->debt));
        };
    }
}
