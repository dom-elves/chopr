<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;

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
    $debt = Debt::where('split_even', 1)->where('user_id', $this->self->id)->first();

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
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 1,
            'amount' => 100,
        ]);
 
    $user_shares = [];

    foreach ($debt->shares as $share) {
        $user_shares[] = [
            'amount' => $share->amount,
            'original_balance' => $share->user->user_balance,
            'user_id' => $share->user_id
        ];
    }

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'name' => $debt->name,
        'amount' => $debt->amount + 10,
    ]);
    
    $split = floor((10 / $debt->shares->count()) * 100) / 100;
    
    foreach ($user_shares as $share_data) {
        $user = User::findOrFail($share_data['user_id']);
        $share_amount = $share_data['amount'];
        $original_balance = $share_data['original_balance'];
        $new_balance = $user->user_balance;

        if ($user->id === $debt->user_id) {
            $difference = $debt->amount - $share_amount;
            $calced_balance = $original_balance + $difference;
            $this->assertSame($new_balance, $calced_balance);
        } else {
            $calced_balance = $original_balance - $split;
            $this->assertSame($new_balance, $calced_balance);
        }
    }
});

test("adding a standard share for yourself doesn't add it to your balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => 100,
        ]);

    $original_balance = $debt->user->user_balance;

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'user_id' => $this->self->id,
        'amount' => 100,
        'name' => 'new share',
    ]);

    $this->self->refresh();

    $this->assertSame($original_balance, $this->self->user_balance);
});

test("adding a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => 100,
        ]);

    $other_user = $debt->users->reject(fn($user) => 
        $user->id === $this->self->id)->first();
    
    $other_user_original_balance = $other_user->user_balance;
    $self_original_balance = $this->self->user_balance;

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'user_id' => $other_user->id,
        'amount' => 100,
        'name' => 'new share',
    ]);

    $this->self->refresh();
    $other_user->refresh();

    $this->assertSame($self_original_balance + 100, $this->self->user_balance);
    $this->assertSame($other_user_original_balance - 100, $other_user->user_balance);
});

test("updating a standard share for yourself doesn't recalculate the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => 100,
        ]);

    $original_balance = $debt->user->user_balance;

    $share = $debt->shares->where('user_id', $this->self->id)->first();
  
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
        'amount' => $share->amount + 10,
        'name' => 'updated name',
    ]);

    $this->self->refresh();

    $this->assertSame($original_balance, $this->self->user_balance);
});

test("updating a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => 100,
        ]);

    $other_share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->self->id)->first();

    $other_user = $other_share->user;
    
    $other_user_original_balance = $other_user->user_balance;
    $self_original_balance = $this->self->user_balance;

    $response = $this->patch(route('share.update'), [
        'id' => $other_share->id,
        'debt_id' => $debt->id,
        'amount' => $other_share->amount + 20,
        'name' => 'new share 2',
    ]);

    $this->self->refresh();
    $other_user->refresh();

    $this->assertSame($self_original_balance + 20, $this->self->user_balance);
    $this->assertSame($other_user_original_balance - 20, $other_user->user_balance);
});

test("deleting a standard share for yourself doesn't recalculate the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => 100,
        ]);

    $original_balance = $debt->user->user_balance;

    $share = $debt->shares->where('user_id', $this->self->id)->first();
  
    $response = $this->delete(route('share.destroy'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
    ]);

    $this->self->refresh();

    $this->assertSame($original_balance, $this->self->user_balance);
});

test("deleting a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => 100,
        ]);

    $other_share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->self->id)->first();

    $other_user = $other_share->user;
    
    $other_user_original_balance = $other_user->user_balance;
    $self_original_balance = $this->self->user_balance;

    $response = $this->delete(route('share.destroy'), [
        'id' => $other_share->id,
        'debt_id' => $debt->id,
    ]);

    $this->self->refresh();
    $other_user->refresh();

    $this->assertSame($self_original_balance - $other_share->amount, $this->self->user_balance);
    $this->assertSame($other_user_original_balance + $other_share->amount, $other_user->user_balance);
});

/**
 * These three tests may be done in a separate piece of work - depends on deicsions made
 * todo: split even mode for active debts?
 */
test("adding a split even share recalculates all involved user balances", function() {

});

test("deleting a split even share recalculates the user's balance", function() {

});

test("updating a split even share recalculates the user's balance", function() {

});
