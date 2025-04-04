<?php

namespace App\Observers;

use App\Models\Debt;
use App\Events\DebtCreated;
use App\Events\DebtUpdated;
use App\Events\DebtDeleted;

class DebtObserver
{
    /**
     * Handle the Debt "created" event.
     */
    public function created(Debt $debt): void
    {
        event(new DebtCreated($debt));
    }

    /**
     * Handle the Debt "updated" event.
     */
    public function updated(Debt $debt): void
    {
        event(new DebtUpdated($debt));
    }

    /**
     * Handle the Debt "deleted" event.
     */
    public function deleted(Debt $debt): void
    {
        event(new DebtDeleted($debt));
    }

    /**
     * Handle the Debt "restored" event.
     */
    public function restored(Debt $debt): void
    {
        //
    }

    /**
     * Handle the Debt "force deleted" event.
     */
    public function forceDeleted(Debt $debt): void
    {
        //
    }
}
