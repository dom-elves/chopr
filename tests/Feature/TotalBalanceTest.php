<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Debt;

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

test("adding a standard debt recalculates the user balances", function() {
    $debt_total = 100;
    $group = Group::where('user_id', $this->self->id)->first();

    $user_shares = selectRandomGroupUsers($group->users, $debt_total, false);

    // first add original balance to user shares data
    foreach ($user_shares as &$share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_data['original_balance'] = $user->user_balance;
    }
    unset($share_data);
 
    // post debt
    $response = $this->post(route('debt.store'), [
        'group_id' => $group->id,
        'user_id' => $this->self->id,
        'name' => 'test debt 123',
        'amount' => $debt_total,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', "test debt 123")->first();
   
    // loop over user shares data again
    // check new user_balanace against original
    foreach ($user_shares as $share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_amount = $share_data['amount'];
        $original_balance = $share_data['original_balance'];

        // if debt owner, check they are "in credit", minus their own share
        // otherwise, check hair has been taken away from original balance
        if ($user->id === $debt->user_id) {
            $difference = $debt->shares->sum('amount') - $share_amount;
            $this->assertTrue($user->user_balance == $original_balance + $difference);
        } else {
            $this->assertTrue($user->user_balance == $original_balance - $share_amount);
        }
    }
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
