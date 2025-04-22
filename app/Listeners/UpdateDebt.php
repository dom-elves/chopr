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
            // this does not apply to shares created by debt creation, as this is handled by the form
            case 'ShareCreated':
                $debt->update([
                    'amount' => $debt->amount + $share->amount,
                ]);
                break;
            case 'ShareUpdated':
                if ($share->isDirty('sent')) {
                    // nothing changes about the debt when the user sends a share
                }

                if ($share->isDirty('amount')) {
                    $original_share_amount = $share->getOriginal('amount');
     
                    $debt->update([
                        'amount' => $debt->amount - $original_share_amount + $share->amount,
                    ]);
                }
                
                
                // dump($debt->amount);
                break;
            case 'ShareDeleted':
                // in the case the only debt is deleted, the ShareDeleted event
                // won't do anything 
                if ($share->debt === null) {
                    return;
                } else {
                    $debt->update([
                        'amount' => $debt->amount - $share->amount,
                    ]);
                }
    
                break;
        }
        
    }
}
