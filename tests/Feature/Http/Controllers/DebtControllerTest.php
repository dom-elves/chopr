<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    // $this->group = Group::where('user_id', $this->user->id)->first();
    $this->group = Group::where('user_id', $this->user->id)->get()[0];

    $this->actingAs($this->user);
});

test('user can add a debt with different value shares', function() {
    $debt_total = 100;
    $user_ids = selectRandomGroupUsers($this->users, $debt_total, false);
    
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
   
    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt created successfully.')
        ->assertRedirect('/dashboard');

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
    $user_ids = selectRandomGroupUsers($this->users, $debt_total, true);

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

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt created successfully.')
        ->assertRedirect('/dashboard');

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
    $user_ids = selectRandomGroupUsers($this->users, $debt_total, false);

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

    $user_ids = selectRandomGroupUsers($this->users, $debt_total, false);

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

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt deleted successfully.')
        ->assertRedirect('/dashboard');

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

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt deleted successfully.')
        ->assertRedirect('/dashboard');

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
        'split_even' => 1,
    ]);

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'name' => $debt->name,
        'amount' => 123,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt updated successfully.')
        ->assertRedirect('/dashboard');

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

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt updated successfully.')
        ->assertRedirect('/dashboard');
  
    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i have been changed',
        'amount' => $debt->amount,
    ]);
});

test('user can not change the name of a debt they do not own', function() {
    $this->actingAs($this->users->last());

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'amount' => $debt->amount,
        'name' => 'i have been changed',
    ]);

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
    $this->actingAs($this->users->last());

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->patch(route('debt.update'), [
        'id' => $debt->id,
        'amount' => $debt->amount,
        'name' => 'change me',
    ]);
    
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
    $this->actingAs($this->users->last());

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
 * select a random amount of users
 * split the debt randomly between the users
 * the last user remaining takes the last share
 * return the key value pair of user_ids and share amounts
 */
function selectRandomGroupUsers($users, $debt_total, $split_even) {
    $users = $users->random(rand(2, $users->count()));

    if (!$split_even) {
        while($users->count() > 0) {
            if ($users->count() === 1) {
                $user = $users->pop();
                $user_ids[$user->id] = $debt_total;
                break;
            }
    
            $user = $users->pop();
            $user_ids[$user->id] = rand(1, $debt_total / $users->count());
            $debt_total -= $user_ids[$user->id];
        }
    } else {
        // because the rounding is done on the frontend, we have to replicate it here
        $share = floor(($debt_total / $users->count()) * 100) / 100;
        $remainder = $debt_total - ($share * $users->count());
        foreach ($users as $user) {
            $user_ids[$user->id] = $share;
        }

        $user_ids[$users[0]->id] += $remainder;
    }

    return $user_ids;
}