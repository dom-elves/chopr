<?php

namespace App\Observers;

use App\Models\Invite;
use App\Jobs\ExpireInvite;

class InviteObserver
{
    public function updated(Invite $invite)
    {
        if ($invite->wasChanged('accepted_at')) {
            ExpireInvite::dispatch($invite);
        }
    }
}

