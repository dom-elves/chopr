<?php

namespace App\Services;

use App\Models\Debt;
use App\Services\ShareService;
use Brick\Money\Money;

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
            'amount' => Money::of($data['amount'], $data['currency']),
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
     * @param array $data - the new data being passed in
     * @return Debt|mixed
     */
    public function updateDebt($data): mixed
    {
        $debt = Debt::findOrFail($data['id']);
        $debt_amount = $debt->amount;
        $new_amount = Money::of($data['amount'], $debt->currency);

        // first we check if we are updating the amount
        if ($debt_amount != $new_amount) {

            $difference = $new_amount->minus($debt_amount);

            // if it's split even, update everyone's shares
            if ($debt->split_even) {
                
                // get the split amount as a money object
                $split_difference = $difference->split($debt->shares->count());
  
                $count = 0;

                foreach ($debt->shares as $share) {
                    // build data object for updating the share
                    $data = [
                        'id' => $share->id,
                        'amount' => $split_difference[$count],
                        'user_id' => $share->user_id,
                    ];
                    
                    $this->shareService->updateShare($data);
                    $count++;
                }

                $debt->amount = $debt->amount->plus($difference);
                $debt->save();
                
            // if not split even, just update the amount
            // discrepancy is handled by the controller
            } else {
                $debt->amount = $debt->amount->plus($difference);
                $debt->save();
            }
        // this is the condition for just updating the debt name    
        } else {
            
            $debt->name = $data['name'];
            $debt->save();
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