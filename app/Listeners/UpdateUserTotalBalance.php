<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ShareUpdated;
use App\Events\ShareDeleted;
use App\Events\ShareCreated;
use App\Events\DebtUpdated;
use App\Events\DebtDeleted;
use App\Events\DebtCreated;

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
    public function handle(DebtCreated|DebtUpdated|DebtDeleted $event): void
    {
        $debt = $event->debt;
        $operation = class_basename($event);
        
        switch($operation) {
            case 'DebtCreated':
                // same as share, we do nothing here as this is handled by the form
                break;
            case 'DebtUpdated':
                // todo: actually do this, it's a nightmare
                break;
            case 'DebtDeleted':
                // cascading deletes don't work with soft deletes
                // so we need to delete the shares manually
                if (!$debt->isForceDeleting()) {
                    $debt->shares->each(function ($share) {
                        $share->delete();
                    });
                }
                break;
        }   
    }
}
