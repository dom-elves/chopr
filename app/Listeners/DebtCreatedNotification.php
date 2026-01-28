<?php

namespace App\Listeners;

use App\Events\DebtCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DebtCreatedNotification
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
        dd($event);
    }
}
