<?php

namespace App\Services;

use App\Models\Share;

class ShareService
{
    public function createDebtShares($data, $debt): void
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

    public function createShare($data): Share
    {
        // create the share
        $share = Share::create([
            'debt_id' => $data['debt_id'],
            'user_id' => $data['user_id'],
            // todo: add name when designing form
            // 'name' => $data['name'],
            'amount' => $data['amount'],
            'sent' => 0,
            'seen' => 0,
        ]);

        // update debt amount
        $debt = $share->debt;
        $debt->amount += $data['amount'];
        $debt->save();

        return $share;
    }

    public function updateShare($data): Share
    {
        // update share with new amount, get original too
        $share = Share::findOrFail($data['id']);
        $original_amount = $share->amount;
        $share->update($data);

        // adjust the debt amount by nnew minus old, using +=
        $debt = $share->debt;
        $debt->amount += $share->amount - $original_amount;
        $debt->save();

        return $share;
    }

    public function deleteDebtShares($data): void
    {
        foreach ($data as $share) {
            $share->delete();
        }

        return; 
    }

    public function deleteShare($data): void
    {
        // just delete the share
        $share = Share::findOrFail($data['id']);
        $share->delete();

        // and adjust the debt amount
        $debt = $share->debt;
        $debt->amount -= $share->amount;
        $debt->save();

        return;
    }
}