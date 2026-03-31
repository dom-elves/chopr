<?php

namespace App\Services;

use App\Models\Debt;
use App\Services\ShareService;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

class DebtService
{
    protected ShareService $shareService;

    public function __construct(ShareService $shareService)
    {
        $this->shareService = $shareService;
    }

    public function createDebt($data, $group): Debt
    {
        $debt = DB::transaction(function () use ($data, $group) {
            $debt = Debt::create([
                    'group_id' => $group->id,
                    'group_user_id' => $data['group_user_id'],
                    'name' => $data['name'],
                    'amount' => $data['amount'],
                    'split_even' => $data['split_even'],
                    'cleared' => 0,
                    'currency' => $data['currency'],
                ]);

            foreach ($data['user_shares'] as $user_share_data) {
                $this->shareService->createShare($user_share_data, $debt);
            }

            return $debt;
        });

        return $debt;
    }
}