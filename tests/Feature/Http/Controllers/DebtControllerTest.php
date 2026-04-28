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
 * - Update amount for owned/not owned
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
        'name' => $this->debt->name,
        'amount' => 20000,
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

test('user can not update the amount on a debt they do not own', function() {
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
 * - Not able to add a debt with a name longe than 255 chars
 * - Not able to add a debt with no currency
 * - Not able to add a debt with no owner (group user) selected
 * - Not able to add a debt with no shares
 * - Not be able to add a debt that sums to zero
 * - Not able to add a debt with no amount
 * - Not able to add a debt for a group they're not in
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

test('user can not add a debt with a name longer than 255 characters', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => str_repeat('a', 256),
        'amount' => 101,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'name' => 'The debt name may not be greater than 255 characters.',
    ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'amount' => 101,
        'name' => str_repeat('a', 256),
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not add a debt with no currency selected', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 56',
        'amount' => 101,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => '',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'currency' => 'Please select a currency.',
    ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'amount' => 101,
        'name' => 'test debt 56',
        'currency' => '',
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not add a debt with no owner selected', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => '',
        'name' => 'test debt 51',
        'amount' => 101,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'group_user_id' => 'Please select a user to own the debt.',
        ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => '',
        'amount' => 101,
        'name' => 'test debt 51',
        'currency' => 'GBP',
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not add a debt with no shares', function() {
    $response = $this->post(route('debt.store'), [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 15',
        'amount' => 101,
        'split_even' => 0,
        'user_shares' => [],
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'user_shares' => 'Please select at least one user or enter a valid amount.',
        ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'amount' => 101,
        'name' => 'test debt 15',
        'currency' => 'GBP',
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not add a debt that sums to zero', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store', [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 25',
        'amount' => 0,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]));

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'amount' => 'The total amount must be at least 0.01.',
        ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'amount' => null,
        'name' => 'test debt 25',
        'currency' => 'GBP',
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not add a debt with no amount', function() {
    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store', [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 25',
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]));

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'amount' => 'The amount field is required.',
        ]);

    $this->assertDatabaseMissing('debts', [
        'group_user_id' => $this->group_user->id,
        'amount' => null,
        'name' => 'test debt 25',
        'currency' => 'GBP',
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);   
});

test('user can not add a debt for a group they are not in', function() {
    $other_group = Group::factory()->create([
        'user_id' => $this->other_group_user->user->id,
    ]);

    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store', [
        'group_id' => $other_group->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 12345,
        'name' => 'test debt 25',
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]));

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'id' => "You do not have permission to create this debt."
        ]);

    $this->assertDatabaseMissing('debts', [
        'group_id' => $other_group->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 12345,
        'name' => 'test debt 25',
        'split_even' => 0,
        'currency' => 'GBP',
    ]);
});

/**
 * Tests for behaviours specific to to split even debts:
 * - Add a split even debt
 * - Updating the amount for a split even debt correctly updates shares
 */

test('user can add a split even debt', function() {
    Event::fake();

    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, true);

    $response = $this->post(route('debt.store', [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 25',
        'amount' => 10000,
        'split_even' => 1,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]));

    Event::assertDispatched(DebtCreated::class);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt created successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 25',
        'amount' => 10000,
        'split_even' => 1,
        'currency' => 'GBP',
    ]);

    foreach ($user_shares as $share) {
        $this->assertDatabaseHas('shares', [
            'group_user_id' => $share['group_user_id'],
            'amount' => $share['amount'],
            'name' => 'share for user ' . $share['group_user_id'],
        ]);
    }
});

test('user can update the amount on a split even debt they own', function() {
    Event::fake();

    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
        'amount' => 12000,
    ]);

    $response = $this->patch(route('debt.update', $debt), [
        'amount' => $debt->amount->plus(15)->getMinorAmount()->toInt(),
        'name' => $debt->name,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt updated successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => $debt->name,
        'amount' => $debt->amount->plus(15)->getMinorAmount()->toInt(),
    ]);

    $splits = $debt->amount->plus(15)->split($debt->shares->count());

    foreach ($debt->shares as $key => $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'group_user_id' => $share->group_user_id,
            'debt_id' => $debt->id,
            'amount' => $splits[$key]->getMinorAmount()->toInt(),
        ]);
    }
});

/**
 * Tests for behaviours specific to standard debts:
 * - Add a standard debt with different value shares
 * - Updating the amount for a standard debt doesn't update shares
 */

test('user can add a standard debt with different value shares', function() {
    Event::fake();

    $user_shares = selectRandomGroupUsers($this->group->groupUsers, 10000, false);

    $response = $this->post(route('debt.store', [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 675',
        'amount' => 10000,
        'split_even' => 0,
        'user_shares' => $user_shares,
        'currency' => 'GBP',
    ]));

    Event::assertDispatched(DebtCreated::class);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Debt created successfully.')
        ->assertRedirect('/debts');

    $this->assertDatabaseHas('debts', [
        'group_id' => $this->group_user->group->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'test debt 675',
        'amount' => 10000,
        'split_even' => 0,
        'currency' => 'GBP',
    ]);

    foreach ($user_shares as $share) {
        $this->assertDatabaseHas('shares', [
            'group_user_id' => $share['group_user_id'],
            'amount' => $share['amount'],
            'name' => 'share for user ' . $share['group_user_id'],
        ]);
    }
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




