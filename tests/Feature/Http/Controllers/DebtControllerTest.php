<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use App\Events\DebtCreated;
use App\Listeners\DebtCreatedNotification;

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

test('debts, shares and comments all appear with permissions paginated', function() {
    Debt::factory(10)->withShares()->withComments()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $this->get('debts')
        ->assertInertia(fn (Assert $page) => 
            $page->component('Debts')
                ->has('debts.data', 5)
                ->has('groups')
                ->has('debts.data.0.can', fn (Assert $can) => $can
                    ->has('update')
                    ->has('delete')
                )
                ->has('debts.data.0.shares.0.can', fn (Assert $can) => $can
                    ->has('update_name')
                    ->has('update_amount')
                    ->has('update_sent')
                    ->has('update_seen')
                    ->has('delete')
                )
                ->has('debts.data.0.comments.0.can', fn (Assert $can) => $can
                    ->has('update')
                    ->has('delete')
                )           
    );
});

test('user can add a debt with different value shares', function() {
    $user_shares = selectRandomGroupUsers($this->users, 100, false);

    Event::fake();

    // save the debt 
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt',
        'amount' => 100,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    Event::assertDispatched(DebtCreated::class);
   
    // 302 is because of inertia redirect
    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt created successfully.')
        ->assertRedirect('/debts');

    // assert it exists
    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group->id,
        'name' => 'test debt',
        'amount' => 10000,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', 'test debt')->first();

    // loop over the values that were posted to check the splits are correct on each share
    foreach ($user_shares as $share) {
        $this->assertDatabaseHas('shares', [
            'user_id' => $share['user_id'],
            'debt_id' => $debt->id,
            'amount' => $share['amount'] * 100,
            'name' => 'share for user ' . $share['user_id'],
        ]);
    }
});

test('user can add a debt that is split even', function() {
    $user_shares = selectRandomGroupUsers($this->users, 100, true);

    Event::fake();

    // save the debt 
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt 2',
        'amount' => 100,
        'split_even' => 1,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt created successfully.')
        ->assertRedirect('/debts');

    // assert it exists
    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt 2',
        'amount' => 10000,
        'split_even' => 1,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);

    $debt = Debt::where('name', 'test debt 2')->first();

    // loop over the values that were posted to check the splits are correct on each share
    foreach ($user_shares as $share) {
        $this->assertDatabaseHas('shares', [
            'user_id' => $share['user_id'],
            'debt_id' => $debt->id,
            'amount' => $share['amount'] * 100,
            'name' => 'share for user ' . $share['user_id'],
        ]);
    }
});

test('user can not add a debt with no group users selected', function() {
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'test debt',
        'amount' => 100,
        'split_even' => 0,
        'user_shares' => [],
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('user_shares');
});

test('user can not add a debt with no name', function() {
    $user_shares = selectRandomGroupUsers($this->users, 150, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => null,
        'amount' => 150,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('debts', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'amount' => 150,
        'name' => null,
    ]);
});

test('user can not add a debt without a selected currency', function() {
    $user_shares = selectRandomGroupUsers($this->users, 151, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i should not exist',
        'amount' => 151,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => '',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('currency');

    $this->assertDatabaseMissing('debts', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i should not exist',
    ]);
});

test('user can not add a debt without a selected user', function() {
    $user_shares = selectRandomGroupUsers($this->users, 170, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group->id,
        'user_id' => '',
        'name' => 'i should not exist',
        'amount' => 170,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('user_id');

    $this->assertDatabaseMissing('debts', [
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'name' => 'i should not exist',
    ]);
 
});

test('user can delete a debt they own', function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->delete(route('debt.destroy', $debt));

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt deleted successfully.')
        ->assertRedirect('/debts');

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

    $response = $this->delete(route('debt.destroy', $debt));

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt deleted successfully.')
        ->assertRedirect('/debts');

    foreach ($shares as $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'user_id' => $share->user_id,
            'debt_id' => $debt->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
});

test('updating the amount on a split even debt updates the shares', function() {
    Event::fake();

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
    ]);

    $shares = $debt->shares;
  
    $new_amount = $debt->amount->plus(10);

    $split = $new_amount->split($shares->count());
  
    $response = $this->patch(route('debt.update', $debt), [
        'id' => $debt->id,
        'name' => $debt->name,
        'amount' => $new_amount->getAmount()->toInt(),
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt & shares updated successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => $debt->name,
        'amount' => $new_amount->getMinorAmount()->toInt(),
    ]);

    foreach ($shares as $key => $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'user_id' => $share->user_id,
            'debt_id' => $debt->id,
            'amount' => $split[$key]->getMinorAmount()->toInt(),
        ]);
    }
});

test('user can update the name of a debt', function() {
    Event::fake();

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $response = $this->patch(route('debt.update', $debt), [
        'id' => $debt->id,
        'name' => 'i have been changed',
        'amount' => $debt->amount->getAmount()->toInt(),
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt updated successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'name' => 'i have been changed',
        'amount' => $debt->amount->getMinorAmount()->toInt(),
    ]);
});

test('user can not change the name of a debt they do not own', function() {
    $this->actingAs($this->users->last());

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->patch(route('debt.update', $debt), [
        'id' => $debt->id,
        'amount' => $debt->amount->getMinorAmount()->toInt(),
        'name' => 'i have been changed',
    ]);

    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to edit this debt.',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $debt->group_id,
        'user_id' => $debt->user_id,
        'name' => $debt->name,
        'amount' => $debt->amount->getMinorAmount()->toInt(),
    ]);
});

test('user can not change the amount of a debt they do not own', function() {
    $this->actingAs($this->users->last());

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->patch(route('debt.update', $debt), [
        'id' => $debt->id,
        'amount' => $debt->amount->getMinorAmount()->toInt(),
        'name' => 'change me',
    ]);
    
    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to edit this debt.',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $debt->group_id,
        'user_id' => $debt->user_id,
        'name' => $debt->name,
        'amount' => $debt->amount->getMinorAmount()->toInt(),
    ]);
});

test('user can not delete a debt they do not own', function() {
    $this->actingAs($this->users->last());

    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $response = $this->delete(route('debt.destroy', $debt));

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to delete this debt.',
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $debt->group_id,
        'user_id' => $debt->user_id,
        'amount' => $debt->amount->getMinorAmount()->toInt(),
    ]);
});

test("user can not add a debt for a group they're not in", function() {
    // at this point in the test suite, the ids are in the 70s
    // so as all requets as still acting as myself, this should suffice
    $group = Group::factory()->create([
        'user_id' => $this->users[1]->id,
    ]);

    $user_shares = selectRandomGroupUsers($this->users, 100, false);

    // save the debt 
    $response = $this->post(route('debt.store'), [
        'group_id' => $group->id,
        'user_id' => $this->user->id,
        'name' => 'unauthorized debt',
        'amount' => 100,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);
   
    $response->assertStatus(302)
        ->assertSessionHasErrors(['id' => "You do not have permission to create this debt."])
        ->assertRedirect('/debts');

    // assert it does not exist
    $this->assertDatabaseMissing('debts', [
        'group_id' => $group->id,
        'name' => 'unauthorized debt',
        'amount' => 10000,
        'split_even' => 0,
        'cleared' => 0,
        'currency' => 'GBP',
    ]);
});


