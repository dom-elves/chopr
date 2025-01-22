<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;

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

    // select a random amount of group users
    $group_users = $group->group_users->random(rand(2, $total_group_users));

    // split the debt randomly between the total group users
    while($group_users->count() > 0) {

        // the last user has the remaining chunk of debt
        if ($group_users->count() === 1) {
            $group_user = $group_users->pop();
            $group_user_values[$group_user->id] = $debt_total;
            break;
        }
        // remove user & random chunk of the total debt
        $group_user = $group_users->pop();
        $group_user_values[$group_user->id] = rand(1, $debt_total / $total_group_users);
        $debt_total -= $group_user_values[$group_user->id];
        $total_group_users--;
    }

    // save the debt 
    $response = $this->post('/debt', [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'amount' => $debt_total,
        'split_even' => 0,
        'group_user_values' => $group_user_values,
    ]);

    $response->assertStatus(200);

    // assert it exists
    $this->assertDatabaseHas('debts', [
        'name' => 'test debt',
        'amount' => $debt_total,
        'split_even' => 0,
        'cleared' => 0
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
    $total_group_users = $this->group->group_users->count();
    $debt_total = 100;

    $response = $this->post('/debt', [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'amount' => 100,
        'split_even' => 0,
        'group_user_values' => [],
    ]);

    // this happens because inertia
    $response->assertStatus(302);
});



