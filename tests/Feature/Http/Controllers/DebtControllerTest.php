<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // create a couple of users
    $users = User::factory(2)->create();
    $this->user = $users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->first();

    $this->actingAs($this->user);
});

test('user can add a debt with different value shares', function() {
    $debt_total = 100;
    $user_ids = selectRandomGroupUsers($this->group, $debt_total, false);

    // save the debt 
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt',
        'amount' => $debt_total,
        'split_even' => 0,
        'user_ids' => $user_ids,
        'currency' => 'GBP',
    ]);
   
    $response->assertStatus(200);

    // assert it exists
    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'amount' => $debt_total,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', 'test debt')->first();

    // loop over the values that were posted to check the splits are correct on each share
    foreach ($user_ids as $key => $value) {
        $this->assertDatabaseHas('shares', [
            'user_id' => $key,
            'debt_id' => $debt->id,
            'amount' => $value,
        ]);
    }
});

test('user can add a debt that is split even', function() {
    $debt_total = 100;
    $user_ids = selectRandomGroupUsers($this->group, $debt_total, true);

    // save the debt 
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt 2',
        'amount' => $debt_total,
        'split_even' => 1,
        'user_ids' => $user_ids,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(200);

    // assert it exists
    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt 2',
        'amount' => $debt_total,
        'split_even' => 1,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', 'test debt 2')->first();

    // loop over the values that were posted to check the splits are correct on each share
    foreach ($user_ids as $key => $value) {
        $this->assertDatabaseHas('shares', [
            'user_id' => $key,
            'debt_id' => $debt->id,
            'amount' => $value,
        ]);
    }
});

test('user can not add a debt with no group users selected', function() {
    $debt_total = 100;

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt',
        'amount' => 100,
        'split_even' => 0,
        'user_ids' => [],
        'currency' => 'GBP',
    ]);

    // this happens because inertia
    $response->assertStatus(302);
    $response->assertSessionHasErrors('user_ids');
});

test('user can not add a debt with no name', function() {
    $debt_total = 100;

    $user_ids = selectRandomGroupUsers($this->group, $debt_total, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => null,
        'amount' => 12345,
        'split_even' => 0,
        'user_ids' => $user_ids,
        'currency' => 'GBP',
    ]);

    // this happens because inertia
    $response->assertStatus(302);
    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('debts', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'amount' => 12345,
    ]);
});

test('user can not add a debt without a selected currency', function() {
    $debt_total = 100;

    $user_ids = selectRandomGroupUsers($this->group, $debt_total, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i should not exist',
        'amount' => 100,
        'split_even' => 0,
        'user_ids' => $user_ids,
        'currency' => '',
    ]);

    // this happens because inertia
    $response->assertStatus(302);
    $response->assertSessionHasErrors('currency');

    $this->assertDatabaseMissing('debts', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i should not exist',
    ]);
});

test('user can delete a debt they own', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id,
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('deleting a debt deletes the relevant shares', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $shares = $debt->shares;

    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id,
    ]);

    $response->assertStatus(200);

    foreach ($shares as $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'user_id' => $share->user_id,
            'debt_id' => $debt->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
});

test('user can update the amount of a debt', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'name' => $debt->name,
        'amount' => 123,
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => $debt->name,
        'amount' => 123,
    ]);
});

test('user can update the name of a debt', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'name' => 'i have been changed',
        'amount' => $debt->amount,
    ]);
  
    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i have been changed',
        'amount' => $debt->amount,
    ]);
});

test('user can not change the name of a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'amount' => $debt->amount,
        'name' => 'i have been changed',
    ]);

    $response->assertStatus(200);
    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to edit or delete this debt',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $debt->group_id,
        'user_id' => $debt->user_id,
        'name' => $debt->name,
        'amount' => $debt->amount,
    ]);
});

test('user can not change the amount of a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'amount' => $debt->amount,
        'name' => 'change me',
    ]);

    $response->assertStatus(200);
    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to edit or delete this debt',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $debt->group_id,
        'user_id' => $debt->user_id,
        'name' => $debt->name,
        'amount' => $debt->amount,
    ]);
});

test('user can not delete a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $response = $this->delete(route('debt.destroy'), [
        'id' => $debt->id,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to edit or delete this debt',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $debt->group_id,
        'user_id' => $debt->user_id,
    ]);
});

/**
 * select a random mount of group users
 * split the debt randomly between the total group users
 * the last user remaining takes the last share
 * return the key value pair
 */
function selectRandomGroupUsers($group, $debt_total, $split_even) {
    $total_group_users = $group->group_users->count();
    $group_users = $group->group_users->random(rand(2, $total_group_users));

    if (!$split_even) {
        while($group_users->count() > 0) {
            if ($group_users->count() === 1) {
                $group_user = $group_users->pop();
                $user_ids[$group_user->id] = $debt_total;
                break;
            }
    
            $group_user = $group_users->pop();
            $user_ids[$group_user->id] = rand(1, $debt_total / $total_group_users);
            $debt_total -= $user_ids[$group_user->id];
        }
    } else {
        $share = $debt_total / $total_group_users;

        foreach ($group_users as $group_user) {
            $user_ids[$group_user->id] = $share;
        }
    }
    
    return $user_ids;
}