<?php

namespace App\Services;

use App\Models\Share;

class ShareService
{
    public function createInitialShares($data, $debt): void
    {
        // create shares
        foreach ($data as $share) {
            Share::create([
                'debt_id' => $debt->id,
                'user_id' => $share['user_id'],
                'name' => $share['name'],
                'amount' => $share['amount'],
                'sent' => 0,
                'seen' => 0,
            ]);
        }
        
        return;
    }

    public function createNewShares($data)
    {

    }
    public function updateShare()
    {

    }

    // only really lives in it's own method as it can be called in a loop
    public function deleteShare($share): void
    {
        // just delete the share
        $share->delete();
    
        return;
    }
}