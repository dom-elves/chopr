<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Debt;
use Brick\Money\Money;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->users = User::factory(5)->create();
    $this->self = $this->users[0];

    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->self->id,
    ]);

    $this->group = Group::first();
    $this->group_users = $this->group->group_users;
    $this->group_user = $this->group_users->where('user_id', $this->self->id)->first();

    $this->actingAs($this->self);
});

// $user_balance is x100 as it is accessed, therefore is /100 for user benefit
// $sum, however is just a sum of the db values, therefore isn't hit by the
// accessor in the same way
test("the seeded db calculates all user's user balance correctly", function() {
    $this->seed();
    $this->assertTrue(checkUserBalances($this->users));
});

test("adding a standard debt recalculates the user balances", function() {
    Event::fake();
    $debt_total = 100;

    $user_shares = selectRandomGroupUsers($this->group_users, $debt_total, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 123',
        'amount' => $debt_total,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($this->users));
});

test("deleting a standard debt recalculates the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $users = $debt->group_users->pluck('user');
    $response = $this->delete(route('debt.destroy', $debt));

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($users));
});

test("updating a standard debt recalculates the user's balance", function() {
    // this test might not need to exist
    // depends on what i decide to do with discrepancies 
});

/**
 * identical to add debt test for standard debts
 */
test("adding a split even debt recalculates the user's balance", function() {
    Event::fake();
    $debt_total = 100;

    $user_shares = selectRandomGroupUsers($this->group_users, $debt_total, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 123',
        'amount' => $debt_total,
        'split_even' => 1,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($this->users));
});

test("deleting a split even debt recalculates the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
    ]);

    $users = $debt->group_users->pluck('user');
    $response = $this->delete(route('debt.destroy', $debt));

    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($users));
});

test("updating a split even debt recalculates the user's balance", function() {
    Event::fake();
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
    ]);

    $response = $this->patch(route('debt.update', $debt), [
        'id' => $debt->id,
        'name' => $debt->name,
        'amount' => $debt->amount->getAmount()->toInt() + 10,
    ]);
    
    $response->assertStatus(302);

    $this->assertTrue(checkUserBalances($debt->group->users));
});

test("adding a standard share for yourself doesn't add it to your balance", function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);
    
    $original_balance = $debt->user->user_balance;

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_user->id,
        'amount' => Money::of(100, 'GBP'),
        'name' => 'new share',
    ]);

    $response->assertStatus(302);

    $this->assertTrue($original_balance == $this->self->user_balance);
});

test("adding a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $other_group_user = $debt->group_users->reject(fn($group_user) => 
        $group_user->user->id === $this->self->id)->first();

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $other_group_user->id,
        'amount' => 1,
        'name' => 'new share',
    ]);

    $response->assertStatus(302);

    $users = collect([$other_group_user->user, $this->self]);
    
    $this->assertTrue(checkUserBalances($users));
});

test("updating the amount of a standard share for yourself doesn't recalculate your balance", function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $own_group_user = $this->group_users->where('user_id', $this->self->id)->first();

    $share = $debt->shares->where('group_user_id', $own_group_user->id)->first();

    $new_amount = $share->amount->plus(10);

    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'debt_id' => $debt->id,
        'amount' => (string) $new_amount->getAmount(),
        'name' => $share->name,

    ]);

    $response->assertStatus(302);

    $users = collect([$this->self]);
    
    $this->assertTrue(checkUserBalances($users));
});

test("updating the amount of a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'group_user_id' => $this->group_user->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $other_group_user = $debt->group_users->reject(fn($group_user) => 
        $group_user->user->id === $this->self->id)->first();

    $other_share = $other_group_user->shares->first();

    $new_amount = $other_share->amount->plus(20);;
    
    $response = $this->patch(route('share.update', $other_share), [
        'id' => $other_share->id,
        'debt_id' => $debt->id,
        'amount' => (string) $new_amount->getAmount(),
        'name' => $other_share->name,
    ]);

    $response->assertStatus(302);
   
    $users = collect([$other_group_user->user, $this->self]);
    
    $this->assertTrue(checkUserBalances($users));
});

test("deleting a standard share for yourself doesn't recalculate the user's balance", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'group_user_id' => $this->group_user->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $own_group_user = $this->group_users->where('user_id', $this->self->id)->first();

    $share = $debt->shares->where('group_user_id', $own_group_user->id)->first();
  
    $response = $this->delete(route('share.destroy', $share));

    $response->assertStatus(302);
   
    $users = collect([$this->self]);
    
    $this->assertTrue(checkUserBalances($users));
});

test("deleting a standard share for another user recalculates both your balances", function() {
    $debt = Debt::factory()->withShares()->create([
            'group_id' => Group::where('user_id', $this->self->id)->first()->id,
            'group_user_id' => $this->group_user->id,
            'split_even' => 0,
            'amount' => Money::of(100, 'GBP'),
        ]);

    $other_group_user = $debt->group_users->reject(fn($group_user) => 
        $group_user->user->id === $this->self->id)->first();

    $other_share = $other_group_user->shares->first();
    
    $response = $this->delete(route('share.destroy', $other_share));

    $response->assertStatus(302);
   
    $users = collect([$other_group_user->user, $this->self]);
    
    $this->assertTrue(checkUserBalances($users));
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
        
        // first, the actual user_balance
        $user_balance = $user->user_balance;

        // then figure out the sum of the individual group_user->balances
        $group_users = GroupUser::where('user_id', $user->id);
        $sum = $group_users->sum('balance');
        
        // if $user_balance is null/0, it won't be accessed as a money object
        if ($user->user_balance == null) {
            return $sum == $user_balance;
        } else {
            return $sum == $user->user_balance->getMinorAmount()->toInt();
        }
    }
};