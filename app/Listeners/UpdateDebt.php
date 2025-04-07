<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ShareCreated;
use App\Events\ShareUpdated;
use App\Events\ShareDeleted;

class UpdateDebt
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
    public function handle(ShareCreated|ShareUpdated|ShareDeleted $event): void
    {
        $share = $event->share;
        $debt = $share->debt;
        $operation = class_basename($event);

        switch($operation) {
            case 'ShareCreated':
                // we don't do anything for creation as this is handled by the form
                break;
            case 'ShareUpdated':
                // todo: actually do this, it's a nightmare
                break;
            case 'ShareDeleted':
                $debt->update([
                    'amount' => $debt->amount - $share->amount,
                ]);
                break;
        }
        
    }
}
