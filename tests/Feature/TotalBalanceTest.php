<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;

beforeEach(function () {
    $this->seed();
    $this->users = User::all();
    $this->self = $this->users[0];
    $this->actingAs($this->self);
});

test("the seeded db calculates all user's user balance correctly", function() {
    foreach ($this->users as $user) {
        $group_users = GroupUser::where('user_id', $user->id);
        $sum = $group_users->sum('balance');
        $user_balance = $user->user_balance;
  
        $this->assertTrue($sum == strval($user_balance));
    }
});

test("adding a standard debt recalculates the user's balance", function() {
    $debt_total = 100;
    $group = Group::where('user_id', $this->self->id)->first();

    $user_shares = selectRandomGroupUsers($group->group_users, $debt_total, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $group->id,
        'user_id' => $this->self->id,
        'name' => 'test debt',
        'amount' => $debt_total,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $group_users = GroupUser::where('user_id', $this->self->id);

    $sum = $group_users->sum('balance');
    $user_balance = $this->self->user_balance;

    $this->assertTrue($sum == strval($user_balance));
});

test("deleting a standard debt recalculates the user's balance", function() {

});

test("updating a standard debt recalculates the user's balance", function() {

});

test("adding a split even debt recalculates the user's balance", function() {

});

test("deleting a split even debt recalculates the user's balance", function() {

});

test("updating a split even debt recalculates the user's balance", function() {

});

test("adding a standard share recalculates the user's balance", function() {

});

test("deleting a standard share recalculates the user's balance", function() {

});

test("updating a standard share recalculates the user's balance", function() {

});

test("adding a split even share recalculates the user's balance", function() {

});

test("deleting a split even share recalculates the user's balance", function() {

});

test("updating a split even share recalculates the user's balance", function() {

});
