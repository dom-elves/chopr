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
        // this seems to be the only way to get numbers correct
        // todo: look into bcmath as that is supposedly better
        $new_balance = strval(round($user->user_balance, 2));

        // if debt owner, check they are "in credit", minus their own share
        // otherwise, check hair has been taken away from original balance
        if ($user->id === $debt->user_id) {
            $difference = $debt->amount - $share_amount;
            $calced_balance = strval(round($original_balance + $difference, 2));
            $this->assertSame($new_balance, $calced_balance);
        } else {
            $calced_balance = strval(round($original_balance - $share_amount, 2));
            $this->assertSame($new_balance, $calced_balance);
        }
    }
});

test("deleting a standard debt recalculates the user's balance", function() {
    $debt = Debt::where('split_even', 0)->first();

    $user_shares = [];

    foreach ($debt->shares as $share) {
        $user_shares[] = [
            'amount' => $share->amount,
            'original_balance' => $share->user->user_balance,
            'user_id' => $share->user_id
        ];
    }
 
    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id
    ]);

    // exactly the same as in test for adding a debt, but inverted +/- operations
    foreach ($user_shares as $share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_amount = $share_data['amount'];
        $original_balance = $share_data['original_balance'];
        $new_balance = strval(round($user->user_balance, 2));
        
        if ($user->id === $debt->user_id) {
            $difference = $debt->amount - $share_amount;
            $calced_balance = strval(round($original_balance - $difference, 2));
            $this->assertSame($new_balance, $calced_balance);
        } else {
            $calced_balance = strval(round($original_balance + $share_amount, 2));
            $this->assertSame($new_balance, $calced_balance);
        }
    }
});

test("updating a standard debt recalculates the user's balance", function() {
    // this test might not need to exist
    // depends on what i decide to do with discrepancies 
});

/**
 * identical to add debt test for standard debts
 */
test("adding a split even debt recalculates the user's balance", function() {
    $debt_total = 100;
    $group = Group::where('user_id', $this->self->id)->first();

    $user_shares = selectRandomGroupUsers($group->users, $debt_total, false);

    foreach ($user_shares as &$share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_data['original_balance'] = $user->user_balance;
    }
    unset($share_data);
 
    // post debt
    $response = $this->post(route('debt.store'), [
        'group_id' => $group->id,
        'user_id' => $this->self->id,
        'name' => 'test debt 456',
        'amount' => $debt_total,
        'split_even' => 1,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', "test debt 456")->first();

    foreach ($user_shares as $share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_amount = $share_data['amount'];
        $original_balance = $share_data['original_balance'];
        $new_balance = strval(round($user->user_balance, 2));

        if ($user->id === $debt->user_id) {
            $difference = $debt->amount - $share_amount;
            $calced_balance = strval(round($original_balance + $difference, 2));
            $this->assertSame($new_balance, $calced_balance);
        } else {
            $calced_balance = strval(round($original_balance - $share_amount, 2));
            $this->assertSame($new_balance, $calced_balance);
        }
    }
});

test("deleting a split even debt recalculates the user's balance", function() {
    $debt = Debt::where('split_even', 1)->first();

    $user_shares = [];

    foreach ($debt->shares as $share) {
        $user_shares[] = [
            'amount' => $share->amount,
            'original_balance' => $share->user->user_balance,
            'user_id' => $share->user_id
        ];
    }
 
    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id
    ]);

    foreach ($user_shares as $share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_amount = $share_data['amount'];
        $original_balance = $share_data['original_balance'];
        $new_balance = strval(round($user->user_balance, 2));
        
        if ($user->id === $debt->user_id) {
            $difference = $debt->amount - $share_amount;
            $calced_balance = strval(round($original_balance - $difference, 2));
            $this->assertSame($new_balance, $calced_balance);
        } else {
            $calced_balance = strval(round($original_balance + $share_amount, 2));
            $this->assertSame($new_balance, $calced_balance);
        }
    }
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
