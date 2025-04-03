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
                //
                break;
            case 'ShareUpdated':
                //
                break;
            case 'ShareDeleted':
                $debt->update([
                    'amount' => $debt->amount - $share->amount,
                ]);
                break;
        }
        
    }
}
