<?php

namespace App\Observers;

use App\Models\Share;
use App\Events\ShareCreated;
use App\Events\ShareUpdated;
use App\Events\ShareDeleted;

class ShareObserver
{
    /**
     * Handle the Share "created" event.
     */
    public function created(Share $share): void
    {
        event(new ShareCreated($share));
    }

    /**
     * Handle the Share "updated" event.
     */
    public function updated(Share $share): void
    {
        // we ignore a user checking 'seen'
        // that is just for user benefit
        if ($share->isDirty(['amount', 'sent'])) {
            event(new ShareUpdated($share));
        }
    }

    /**
     * Handle the Share "deleted" event.
     */
    public function deleted(Share $share): void
    {
        event(new ShareDeleted($share));
    }

    /**
     * Handle the Share "restored" event.
     */
    public function restored(Share $share): void
    {
        //
    }

    /**
     * Handle the Share "force deleted" event.
     */
    public function forceDeleted(Share $share): void
    {
        //
    }
}
