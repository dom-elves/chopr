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
        // this is broken for some reason and $event has no share on it like others
        event(new ShareCreated($share));
    }

    /**
     * Handle the Share "updated" event.
     */
    public function updated(Share $share): void
    {
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
