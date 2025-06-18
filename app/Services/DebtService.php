<?php

namespace App\Services;

use App\Models\Debt;
use App\Services\ShareService;

class DebtService
{
    protected ShareService $shareService;

    public function __construct(ShareService $shareService)
    {
        $this->shareService = $shareService;
    }

    /**
     * Create a new debt.
     *
     * @param array $data
     * @return Debt|mixed
     */
    public function createDebt($data): Debt 
    {
        // create the debt with validated data
        $debt = Debt::create([
            'group_id' => $data['group_id'],
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'amount' => $data['amount'],
            'split_even' => $data['split_even'],
            'cleared' => 0,
            'currency' => $data['currency'],
        ]);

        // create the relative shares
        $this->shareService->createDebtShares($data['user_shares'], $debt);

        return $debt;
    }

    /**
     * Update an existing debt.
     *
     * @param array $data
     * @return Debt|mixed
     */
    public function updateDebt($data): mixed
    {
        // store original values
        $debt = Debt::findOrFail($data['id']);
        $original = $debt->getOriginal();
        
        // for updating the amount, we have to do quite a few things
        if ($original['amount'] != $data['amount']) {

            // if it's split even, update everyone's shares
            if ($debt->split_even) {
                $difference = $data['amount'] - $original['amount'];
             
                $floor_split = floor($difference / $debt->shares->count() * 100) / 100;
                $total_splits = $floor_split * $debt->shares->count();
                $remainder = $difference - $total_splits;

                $count = 0;

                foreach ($debt->shares as $share) {
                    $data = [
                        'id' => $share->id,
                        'amount' => $share->amount + ($count === 0 ? $floor_split + $remainder : $floor_split),
                        'user_id' => $share->user_id,
                    ];
          
                    $this->shareService->updateShare($data);
                    $count++;
                }
                
            // if not split even, just update the amount
            // discrepancy is handled by the controller
            } else {
                $debt->update($data);
            }
        // this is the condition for just updating the debt name    
        } else {
            $debt->update($data);
        }

        return $debt;
    }

    public function deleteDebt($data): void
    {
        // find the debt
        $debt = Debt::findOrFail($data['id']);

        // delete the shares
        $this->shareService->deleteDebtShares($debt->shares);

        // and finally the debt
        $debt->delete();

        return;
    }
}