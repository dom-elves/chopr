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