<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // Reset the database
    $this->artisan('migrate:fresh --seed');

    // seeder is built so i'm first user & at least in multiple groups with debts etc
    $this->user = User::first();
    $this->group_user = GroupUser::where('user_id', $this->user->id)->first();
    $this->group = $this->group_user->group;

    $this->actingAs($this->user);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

// todo: move this when eventually moving dash logic to controller
test('dashboard can be rendered', function() {
    $response = $this->get('/dashboard');

    $response->assertStatus(200);
});

test('user can add a debt', function() {
    $total_group_users = $this->group->group_users->count();
    $debt_total = 100;

    $group_user_values = selectRandomGroupUsers($this->group, $debt_total);

    // save the debt 
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'amount' => $debt_total,
        'split_even' => 0,
        'group_user_values' => $group_user_values,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(200);

    // assert it exists
    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'collector_group_user_id' => $this->group_user->id,
        'amount' => $debt_total,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', 'test debt')->first();

    // loop over the values that were posted to check the splits are correct on each share
    foreach ($group_user_values as $key => $value) {
        $this->assertDatabaseHas('shares', [
            'group_user_id' => $key,
            'debt_id' => $debt->id,
            'amount' => $value,
            'paid_amount' => 0,
            'cleared' => 0
        ]);
    }
});

test('user can not add a debt with no group users selected', function() {
    $debt_total = 100;

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'amount' => 100,
        'split_even' => 0,
        'group_user_values' => [],
        'currency' => 'GBP',
    ]);

    // this happens because inertia
    $response->assertStatus(302);
    $response->assertSessionHasErrors('group_user_values');
});

test('user can not add a debt with no name', function() {
    $debt_total = 100;

    $group_user_values = selectRandomGroupUsers($this->group, $debt_total);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => null,
        'amount' => 100,
        'split_even' => 0,
        'group_user_values' => $group_user_values,
        'currency' => 'GBP',
    ]);

    // this happens because inertia
    $response->assertStatus(302);
    $response->assertSessionHasErrors('name');
});

test('user can not add a debt without a selected currency', function() {
    $debt_total = 100;

    $group_user_values = selectRandomGroupUsers($this->group, $debt_total);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => null,
        'amount' => 100,
        'split_even' => 0,
        'group_user_values' => $group_user_values,
        'currency' => '',
    ]);

    // this happens because inertia
    $response->assertStatus(302);
    $response->assertSessionHasErrors('currency');
});

test('user can delete a debt they own', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'delete me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $response = $this->delete(route('debt.destroy'), [
        'debt_id' => $debt->id,
        'owner_group_user_id' => $this->group_user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'delete me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('deleting a debt deletes the relevant shares', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'delete me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $shares = $debt->shares;

    $response = $this->delete(route('debt.destroy'), [
        'debt_id' => $debt->id,
    ]);

    foreach ($shares as $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'debt_id' => $debt->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
});

test('user can update the amount of a debt', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'change my amount',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $response = $this->patch(route('debt.update'), [
        'debt_id' => $debt->id,
        'amount' => 500,
        'name' => 'change my amount',
        'owner_group_user_id' => $this->group_user->id,
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'change my amount',
        'amount' => 500,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);
});

test('user can update the name of a debt', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'update me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $response = $this->patch(route('debt.update'), [
        'debt_id' => $debt->id,
        'amount' => 100,
        'name' => 'i have been changed',
        'owner_group_user_id' => $this->group_user->id,
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id,
        'name' => 'i have been changed',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);
});

test('user can not change the name of a debt they do not own', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id + 1,
        'name' => 'change me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $response = $this->patch(route('debt.update'), [
        'debt_id' => $debt->id,
        'amount' => 100,
        'name' => 'i have been changed',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id + 1,
        'name' => 'change me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);
});

test('user can not change the amount of a debt they do not own', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id + 1,
        'name' => 'change me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $response = $this->patch(route('debt.update'), [
        'debt_id' => $debt->id,
        'amount' => 123,
        'name' => 'change me',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id + 1,
        'name' => 'change me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);
});

test('user can not delete a debt they do not own', function() {
    $debt = Debt::create([
        'group_id' => $this->group->id,
        'collector_group_user_id' => $this->group_user->id + 1,
        'name' => 'delete me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $response = $this->delete(route('debt.destroy'), [
        'debt_id' => $debt->id,
        'owner_group_user_id' => $debt->collector_group_user_id,
    ]);

    $response->assertStatus(302);
    $response->assertInvalid([
        'owner_group_user_id' => 'You do not have permission to edit or delete this debt',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'collector_group_user_id' => $debt->collector_group_user_id,
        'name' => 'delete me',
        'amount' => 100,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
        'deleted_at' => null
    ]);
});

/**
 * select a random mount of group users
 * split the debt randomly between the total group users
 * the last user remaining takes the last share
 * return the key value pair
 */
function selectRandomGroupUsers($group, $debt_total) {
    $total_group_users = $group->group_users->count();
    $group_users = $group->group_users->random(rand(2, $total_group_users));

    // split the debt randomly between the total group users
    while($group_users->count() > 0) {
        if ($group_users->count() === 1) {
            $group_user = $group_users->pop();
            $group_user_values[$group_user->id] = $debt_total;
            break;
        }

        $group_user = $group_users->pop();
        $group_user_values[$group_user->id] = rand(1, $debt_total / $total_group_users);
        $debt_total -= $group_user_values[$group_user->id];
    }

    return $group_user_values;
}