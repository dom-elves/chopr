<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use Brick\Money\Money;

beforeEach(function () {
    $this->seed();
    $this->users = User::all();
    $this->self = $this->users[0];
    $this->actingAs($this->self);
});

// $user_balance is x100 as it is accessed, therefore is /100 for user benefit
// $sum, however is just a sum of the db values, therefore isn't hit by the
// accessor in the same way
test("the seeded db calculates all user's user balance correctly", function() {
    $this->assertTrue(checkUserBalances($this->users));
});

test("adding a standard debt recalculates the user balances", function() {
    $debt_total = 100;
    $group = Group::where('user_id', $this->self->id)->first();

    $user_shares = selectRandomGroupUsers($group->users, $debt_total, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $group->id,
        'user_id' => $this->self->id,
        'name' => 'test debt 123',
        'amount' => $debt_total,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($group->users));
});

test("deleting a standard debt recalculates the user's balance", function() {
    $debt = Debt::where('split_even', 0)->first();

    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id
    ]);

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($debt->group->users));
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

    $response = $this->post(route('debt.store'), [
        'group_id' => $group->id,
        'user_id' => $this->self->id,
        'name' => 'test debt 123',
        'amount' => $debt_total,
        'split_even' => 1,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($group->users));
});

test("deleting a split even debt recalculates the user's balance", function() {
    $debt = Debt::where('split_even', 1)->first();

    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id
    ]);

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($debt->group->users));
});

test("updating a split even debt recalculates the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 1,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'name' => $debt->name,
        'amount' => $debt->amount->getAmount()->toInt() + 10,
    ]);
    
    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($debt->group->users));
});

test("adding a standard share for yourself doesn't add it to your balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $original_balance = $debt->user->user_balance;

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'user_id' => $this->self->id,
        'amount' => Money::of(100, 'GBP'),
        'name' => 'new share',
    ]);

    $response->assertStatus(302);

    $this->assertTrue($original_balance == $this->self->user_balance);
});

test("adding a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $other_user = $debt->users->reject(fn($user) => 
        $user->id === $this->self->id)->first();

    $other_user_original_balance = $other_user->user_balance;
    $self_original_balance = $this->self->user_balance;

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'user_id' => $other_user->id,
        'amount' => 1,
        'name' => 'new share',
    ]);

    $response->assertStatus(302);

    $users = collect([$other_user, $this->self]);
    
    checkUserBalances($users);
});

test("updating the amount of a standard share for yourself doesn't recalculate your balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $original_balance = $debt->user->user_balance;

    $share = $debt->shares->where('user_id', $this->self->id)->first();

    $new_amount = $share->amount->plus(10);

    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
        'amount' => (string) $new_amount->getAmount(),

    ]);

    $response->assertStatus(302);

    $users = collect([$this->self]);
    
    checkUserBalances($users);
});

test("updating the amount of a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $other_share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->self->id)->first();

    $new_amount = $other_share->amount->plus(20);

    $other_user = $other_share->user;
    
    $other_user_original_balance = $other_user->user_balance;
    $self_original_balance = $this->self->user_balance;

    $response = $this->patch(route('share.update'), [
        'id' => $other_share->id,
        'debt_id' => $debt->id,
        'amount' => (string) $new_amount->getAmount(),
    ]);

    $response->assertStatus(302);
   
    $users = collect([$other_user, $this->self]);
    
    checkUserBalances($users);
});

test("deleting a standard share for yourself doesn't recalculate the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $original_balance = $debt->user->user_balance;

    $share = $debt->shares->where('user_id', $this->self->id)->first();
  
    $response = $this->delete(route('share.destroy'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
    ]);

    $response->assertStatus(302);
   
    $users = collect([$this->self]);
    
    checkUserBalances($users);
});

test("deleting a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'user_id' => $this->self->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
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

    $response->assertStatus(302);
   
    $users = collect([$other_user, $this->self]);
    
    checkUserBalances($users);
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

function checkUserBalances($users) {
    foreach ($users as $user) {
        $group_users = GroupUser::where('user_id', $user->id);
        $sum = $group_users->sum('balance');
        $user_balance = $user->user_balance;

        // if $user_balance is null/0, it won't be accessed as a money object
        if ($user->user_balance == null) {
            return $sum == $user_balance;
        } else {
            return $sum == $user->user_balance->getMinorAmount()->toInt();
        }
    }
};