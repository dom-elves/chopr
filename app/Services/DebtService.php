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
}