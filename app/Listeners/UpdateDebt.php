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
                // this is kinda of cheating as changing 'sent' doesn't have an immediate
                // effect on the debt, the only way to change this would be to 
                // re-add 'paid_amount' to debts then change that value
                // every time the share is changed between sent/not sent
                // todo: maybe do this^ 
                if ($share->isDirty('sent')) {
                   switch ($share->sent) {
                        case true:
                            $debt->user->total_balance -= $share->amount;
                            $share->user->total_balance += $share->amount;
                            break;
                        case false:
                            $debt->user->total_balance += $share->amount;
                            $share->user->total_balance -= $share->amount;
                            break;
                    }
                    
                    $debt->user->save();
                    $share->user->save();
                }

                if ($share->isDirty('amount')) {

                    // if the user is changing amount after debt has been sent
                    // we don't want to mess this their current balance
                    if ($share->sent) {
                        return;
                    };

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
