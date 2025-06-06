<?php
use App\Models\User;
use App\Models\GroupUser;

beforeEach(function () {
    $this->seed();
    $this->users = User::all();
});

test("the seeded db calculates all user's user balance correctly", function() {
    foreach ($this->users as $user) {
        $group_users = GroupUser::where('user_id', $user->id);
        $sum = $group_users->sum('balance');
        $user_balance = $user->user_balance;

        $this->assertTrue($sum == $user_balance);
    }
});