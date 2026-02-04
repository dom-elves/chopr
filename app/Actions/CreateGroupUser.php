<?php

namespace App\Actions;

use App\Models\GroupUser;

class CreateGroupUser
{
    public static function execute(int $user_id, int $group_id): GroupUser
    {
        return GroupUser::create([
            'user_id' => $user_id,
            'group_id' => $group_id,
            'balance' => 0,
        ]);
    }
}