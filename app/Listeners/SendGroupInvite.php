<?php

namespace App\Listeners;

use App\Events\InviteCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToGroup;
use App\Jobs\ExpireInvite;
use Carbon\Carbon;

class SendGroupInvite
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
    public function handle(InviteCreated $event): void
    {
        Mail::to($event->invite->recipient)
            ->queue(new InviteToGroup($event->invite));

        ExpireInvite::dispatch($event->invite)
            ->delay(Carbon::now()->addDays(1));
    }
}
