<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use App\Events\DebtCreated;
use App\Events\DebtUpdated;
use Illuminate\Support\Arr;
use Brick\Money\Money;

beforeEach(function () {
    $this->users = User::factory(10)->create();
    $this->user = $this->users[0];

    $this->group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->group_user = GroupUser::where('user_id', $this->user->id)->first();

    $this->other_group_user = $this->group->groupUsers->reject(fn($group_user) 
        => $group_user->id === $this->group_user->id)->first();

    $this->debt = Debt::factory()->withShares()->withComments()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $this->actingAs($this->group_user->user);
});

/**
 * Tests for shared behaviours, not around adding debts:
 * - Index and pagination
 * - Update name for owned/not owned
 * - Delete for owned/not owned
 * - Deleting debts deletes shares & comments
 */
test('debts, shares and comments all appear with permissions paginated', function() {
    Debt::factory(10)->withShares()->withComments()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
    ]);

    $this->get('debts')
        ->assertInertia(fn (Assert $page) => 
            $page->component('Debts')
                ->has('debts.data', 10)
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

test('user can update the name of a debt they own', function() {
    Event::fake();

    $response = $this->patch(route('debt.update', $this->debt), [
        'name' => 'i have been changed',
        'amount' => $this->debt->amount->getMinorAmount()->toInt(),
    ]);

    Event::assertDispatched(DebtUpdated::class);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt updated successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'i have been changed',
        'amount' => $this->debt->amount->getMinorAmount()->toInt(),
    ]);
});

test('user can not update the name on a debt they do not own', function() {
    Event::fake();

    $this->actingAs($this->other_group_user->user);

    $response = $this->patch(route('debt.update', $this->debt), [
        'name' => 'i have been changed',
        'amount' => $this->debt->amount->getMinorAmount()->toInt(),
    ]);

    Event::assertNotDispatched(DebtUpdated::class);

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'id' => "You do not have permission to edit this debt."
        ])
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => $this->debt->name,
        'amount' => $this->debt->amount->getMinorAmount()->toInt(),
    ]);
});

test('user can delete a debt they own and along with all associated shares and comments', function() {  
    $response = $this->delete(route('debt.destroy', $this->debt));

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt deleted successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
    
    foreach ($this->debt->shares as $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'debt_id' => $this->debt->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    foreach ($this->debt->comments as $comment) {
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'debt_id' => $this->debt->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
});

test('user can not delete a debt they do not own', function() {
    $this->actingAs($this->other_group_user->user);

    $response = $this->delete(route('debt.destroy', $this->debt));

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'id' => "You do not have permission to delete this debt."
        ])
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'group_id' => $this->group->id,
        'group_user_id' => $this->debt->group_user_id,
        'name' => $this->debt->name,
        'amount' => $this->debt->amount->getMinorAmount()->toInt(),
        'deleted_at' => null,
    ]);

    foreach ($this->debt->shares as $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'debt_id' => $this->debt->id,
            'deleted_at' => null,
        ]);
    }
});

/**
 * Tests for behaviours to do with adding debts, but the type is irrelevant:
 * - Not able to add a debt with no group selected
 * - Not able to add a debt with no name
 * - Not able to add a debt with no currency
 * - Not able to add a debt with no owner (group user) selected
 * - Not able to add a debt with no shares
 * - Not able to add a debt with no amount
 */

test('user can not add a debt with no group selected', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store'), [
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt',
        'amount' => 100,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'group_id' => 'Please select a group.',
    ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt',
        'amount' => 100,
    ]);
});

test('user can not add a debt with no name', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 101,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'name' => 'The debt name is required.',
    ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'amount' => 101,
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});


// test('user can add a debt with different value shares', function() {
//     $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);
    
//     Event::fake();

//     // save the debt 
//     $response = $this->post(route('debt.store'), [
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => 'test debt',
//         'amount' => 10000,
//         'split_even' => 0,
//         'user_shares' => $user_shares,
//         'currency' => 'GBP',
//     ]);

//     Event::assertDispatched(DebtCreated::class);
   
//     $response->assertStatus(302)
//         ->assertSessionHasNoErrors()
//         ->assertSessionHas('status', 'Debt created successfully.')
//         ->assertRedirect('/debts');

//     // assert it exists
//     $this->assertDatabaseHas('debts', [
//         'group_id' => $this->group->id,
//         'name' => 'test debt',
//         'amount' => 10000,
//         'split_even' => 0,
//         'cleared' => 0,
//         'currency' => 'GBP',
//     ]);

//     $debt = Debt::where('name', 'test debt')->first();

//     // loop over the values that were posted to check the splits are correct on each share
//     foreach ($user_shares as $share) {
//         $this->assertDatabaseHas('shares', [
//             'group_user_id' => $share['group_user_id'],
//             'debt_id' => $debt->id,
//             'amount' => $share['amount'],
//             'name' => 'share for user ' . $share['group_user_id'],
//         ]);
//     }
// });

// /**
//  * Bit of context for split even as it acts a bit differently to random amount debts
//  * as they use selectRandomGroupUsers()
//  * 
//  * Just got kinda sick of having to write annoying separate conditions in the user
//  * selection function, so it was easier to just take the hit and have separate <groups>
//  * and users for the setup of this one.
//  */
// test('user can add a debt that is split even', function() {
//     $users = User::factory(5)->create();
//     $this->actingAs($users[0]);

//     $group = Group::factory(1)
//         ->create([
//             'user_id' => $users->first()->id,
//         ]);
    
//     foreach ($users as $user) {
//         GroupUser::factory()->create([
//             'user_id' => $user->id,
//             'group_id' => $group[0]->id,
//         ]);
//     }

//     $group_users = GroupUser::where('group_id', $group[0]->id)->get();
//     $shares = Money::ofMinor(10000, 'GBP')->split($group_users->count());
    
//     $user_shares = $group_users->map(function ($group_user, $key) use ($shares) {
//         return $group_user_shares[] = [
//             'group_user_id' => $group_user->id,
//             'share_name' => 'share for user ' . $group_user->id,
//             'amount' => $shares[$key]->getMinorAmount(),
//             'user_name' => $group_user->user->name,
//         ];
//     })->toArray();

//     Event::fake();

//     $response = $this->post(route('debt.store'), [
//         'group_id' => $group[0]->id,
//         'group_user_id' => $group_users[0]->id,
//         'name' => 'test debt 2',
//         'amount' => 10000,
//         'split_even' => 1,
//         'user_shares' => $user_shares,
//         'currency' => 'GBP',
//     ]);

//     $response->assertStatus(302)
//         ->assertSessionHasNoErrors()
//         ->assertSessionHas('status', 'Debt created successfully.')
//         ->assertRedirect('/debts');

//     $this->assertDatabaseHas('debts', [
//         'group_id' => $group[0]->id,
//         'group_user_id' => $group_users[0]->id,
//         'name' => 'test debt 2',
//         'amount' => 10000,
//         'split_even' => 1,
//         'cleared' => 0,
//         'currency' => 'GBP',
//     ]);

//     $debt = Debt::where('group_id', $group[0]->id)->first();

//     foreach ($user_shares as $share) {
//         $this->assertDatabaseHas('shares', [
//             'group_user_id' => $share['group_user_id'],
//             'debt_id' => $debt->id,
//             'amount' => $share['amount'],
//             'name' => 'share for user ' . $share['group_user_id'],
//         ]);
//     }
// });

// test('user can not add a debt with no group users selected', function() {
//     $response = $this->post(route('debt.store'), [
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => 'test debt',
//         'amount' => 100,
//         'split_even' => 0,
//         'user_shares' => [],
//         'currency' => 'GBP',
//     ]);

//     $response->assertStatus(302);
//     $response->assertSessionHasErrors('user_shares');
// });

// test('user can not add a debt with no name', function() {
//     $user_shares = selectRandomGroupUsers($this->group->groupUsers, 15000, false);

//     $response = $this->post(route('debt.store'), [
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => null,
//         'amount' => 150,
//         'split_even' => 0,
//         'user_shares' => $user_shares,
//         'currency' => 'GBP',
//     ]);

//     $response->assertStatus(302);
//     $response->assertSessionHasErrors('name');

//     $this->assertDatabaseMissing('debts', [
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'amount' => 150,
//         'name' => null,
//     ]);
// });

// test('user can not add a debt without a selected currency', function() {
//     $user_shares = selectRandomGroupUsers($this->group->groupUsers, 20000, false);

//     $response = $this->post(route('debt.store'), [
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => 'i should not exist',
//         'amount' => 151,
//         'split_even' => 0,
//         'user_shares' => $user_shares,
//         'currency' => '',
//     ]);

//     $response->assertStatus(302);
//     $response->assertSessionHasErrors('currency');

//     $this->assertDatabaseMissing('debts', [
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => 'i should not exist',
//     ]);
// });

// test('user can not add a debt without a selected user', function() {
//     $user_shares = selectRandomGroupUsers($this->group->groupUsers, 25000, false);

//     $response = $this->post(route('debt.store'), [
//         'group_id' => $this->group->id,
//         'group_user_id' => '',
//         'name' => 'i should not exist',
//         'amount' => 170,
//         'split_even' => 0,
//         'user_shares' => $user_shares,
//         'currency' => 'GBP',
//     ]);

//     $response->assertStatus(302);
//     $response->assertSessionHasErrors('group_user_id');

//     $this->assertDatabaseMissing('debts', [
//         'group_user_id' => $this->group_user->id,
//         'group_id' => $this->group->id,
//         'name' => 'i should not exist',
//     ]);
 
// });


// test('updating the amount on a split even debt updates the shares', function() {
//     Event::fake();

//     $debt = Debt::factory()->withShares()->create([
//         'group_user_id' => $this->group_user->id,
//         'group_id' => $this->group->id,
//         'split_even' => 1,
//     ]);

//     $shares = $debt->shares;
  
//     $new_amount = $debt->amount->plus(10);

//     $split = $new_amount->split($shares->count());
  
//     $response = $this->patch(route('debt.update', $debt), [
//         'name' => $debt->name,
//         'amount' => $new_amount->getMinorAmount()->toInt(),
//     ]);

//     $response->assertStatus(302)
//         ->assertSessionHasNoErrors()
//         ->assertSessionHas('status', 'Debt & shares updated successfully.')
//         ->assertRedirect('/debts');
    
//     $this->assertDatabaseHas('debts', [
//         'id' => $debt->id,
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => $debt->name,
//         'amount' => $new_amount->getMinorAmount()->toInt(),
//     ]);

//     foreach ($shares as $key => $share) {
//         $this->assertDatabaseHas('shares', [
//             'id' => $share->id,
//             'group_user_id' => $share->group_user_id,
//             'debt_id' => $debt->id,
//             'amount' => $split[$key]->getMinorAmount()->toInt(),
//         ]);
//     }
// });

// test('user can update the name of a debt', function() {
//     Event::fake();

//     $debt = Debt::factory()->withShares()->create([
//         'group_user_id' => $this->group_user->id,
//         'group_id' => $this->group->id,
//         'split_even' => 0,
//     ]);

//     $response = $this->patch(route('debt.update', $debt), [
//         'name' => 'i have been changed',
//         'amount' => $debt->amount->getMinorAmount()->toInt(),
//     ]);

//     $response->assertStatus(302)
//         ->assertSessionHasNoErrors()
//         ->assertSessionHas('status', 'Debt updated successfully.')
//         ->assertRedirect('/debts');

//     $this->assertDatabaseHas('debts', [
//         'id' => $debt->id,
//         'group_id' => $this->group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => 'i have been changed',
//         'amount' => $debt->amount->getMinorAmount()->toInt(),
//     ]);
// });

// test('user can not change the name of a debt they do not own', function() {
//     $this->actingAs($this->users->last());

//     $debt = Debt::factory()->withShares()->create([
//         'group_user_id' => $this->group_user->id,
//         'group_id' => $this->group->id,
//     ]);

//     $response = $this->patch(route('debt.update', $debt), [
//         'amount' => $debt->amount->getMinorAmount()->toInt(),
//         'name' => 'i have been changed',
//     ]);

//     $response->assertSessionHasErrors([
//         'id' => 'You do not have permission to edit this debt.',
//     ]);

//     $this->assertDatabaseHas('debts', [
//         'id' => $debt->id,
//         'group_id' => $debt->group_id,
//         'group_user_id' => $debt->groupUser->id,
//         'name' => $debt->name,
//         'amount' => $debt->amount->getMinorAmount()->toInt(),
//     ]);
// });

// test('user can not change the amount of a debt they do not own', function() {
//     $this->actingAs($this->users->last());

//     $debt = Debt::factory()->withShares()->create([
//         'group_user_id' => $this->group_user->id,
//         'group_id' => $this->group->id,
//     ]);

//     $response = $this->patch(route('debt.update', $debt), [
//         'amount' => $debt->amount->getMinorAmount()->toInt(),
//         'name' => 'change me',
//     ]);
    
//     $response->assertSessionHasErrors([
//         'id' => 'You do not have permission to edit this debt.',
//     ]);

//     $this->assertDatabaseHas('debts', [
//         'id' => $debt->id,
//         'group_id' => $debt->group_id,
//         'group_user_id' => $debt->groupUser->id,
//         'name' => $debt->name,
//         'amount' => $debt->amount->getMinorAmount()->toInt(),
//     ]);
// });


// test("user can not add a debt for a group they're not in", function() {
//     // at this point in the test suite, the ids are in the 70s
//     // so as all requets as still acting as myself, this should suffice
//     $group = Group::factory()->create([
//         'user_id' => $this->users[1]->id,
//     ]);

//     $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

//     // save the debt 
//     $response = $this->post(route('debt.store'), [
//         'group_id' => $group->id,
//         'group_user_id' => $this->group_user->id,
//         'name' => 'unauthorized debt',
//         'amount' => 100,
//         'split_even' => 0,
//         'user_shares' => $user_shares,
//         'currency' => 'GBP',
//     ]);
   
//     $response->assertStatus(302)
//         ->assertSessionHasErrors(['id' => "You do not have permission to create this debt."])
//         ->assertRedirect('/debts');

//     // assert it does not exist
//     $this->assertDatabaseMissing('debts', [
//         'group_id' => $group->id,
//         'name' => 'unauthorized debt',
//         'amount' => 10000,
//         'split_even' => 0,
//         'cleared' => 0,
//         'currency' => 'GBP',
//     ]);
// });


