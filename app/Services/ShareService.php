<?php

namespace App\Services;

use App\Models\Share;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Services\LedgerService;
use Brick\Money\Money;

class ShareService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * 'Create' methods
     */

    /**
     * Specific to when shares are created during debt creation.
     * @param Debt $debt
     * @param $user_shares_data array of share data from form request
     * @return void
     */
    public function createShares($debt, $user_shares_data): void
    {
        foreach ($user_shares_data as $share_data) {
            $this->createShare($debt, $share_data);
        }
    }

    /**
     * For the purpose of creating a single share for an existing debt.
     * @param Debt $debt
     * @param array $share_data
     * @return Share
     */
    public function createSingleShare($debt, $share_data): Share
    {
        $share = $this->createShare($debt, $share_data);

        // logic for updating debt total
        // depending on split/standard debt
        // may well end up being very annoying to redo

        return $share;
    }

    /**
     * As both creating single & multiple shares require mostly the same logic,
     * keep the repeatable bits here.
     * @param Debt $debt
     * @param array $share_data
     * @return Share
     */
    private function createShare($debt, $share_data): Share
    {
        // todo: see if there's a better way to do this without query
        $share_user_id = GroupUser::findOrFail($share_data['group_user_id'])->user_id;

        $share = Share::create([
            'debt_id' => $debt->id,
            'group_user_id' => $share_data['group_user_id'],
            'name' => $share_data['share_name'],
            'amount' => $share_data['amount'],
            'sent' => $share_user_id === auth()->user()->id ? 1 : 0,
            'seen' => $share_user_id === auth()->user()->id ? 1 : 0,
        ]);

        $this->ledgerService->createLedgerEntry($share);

        return $share;
    }

    /**
     * 'Update' methods
     */

    /**
     * For the purpose of updating shares when a split even debt is updated,
     * when a regular debt is updated, shares are not edited and no ledger is required.
     * @param Debt $debt
     * @return void
     */
    public function updateShares($debt): void
    {
        $updated_shares = Money::ofMinor($debt->amount, $debt->currency)->split($debt->shares->count());
   
        foreach ($debt->shares as $key => $share) {
            $data['amount'] = $updated_shares[$key]->getMinorAmount()->toInt();
        
            $this->updateShare($share, $data);
        }
    }
    /**
     * For updating the name/amount of a single share.
     * @param Share $share
     * @param array $data
     * @return Share
     */
    public function updateSingleShare(Share $share, $data): Share 
    {
        $share = $this->updateShare($share, $data);

        return $share;
    }

    public function updateShare(Share $share, $data): Share
    {
        $this->ledgerService->updatedLedgerEntry($share, $data['amount']);

        $share->update([
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);

        return $share;
    }
}