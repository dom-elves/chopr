<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Share;
use Carbon\Carbon;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Sequence;

beforeEach(function () {
    $this->users = User::factory(10)->create();
    $this->user = $this->users[0];

    $this->group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->group_users = $this->group->groupUsers;
    $this->group_user = GroupUser::where('user_id', $this->user->id)->first();

    $this->debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $this->share = Share::factory()->create([
        'group_user_id' => $this->group_user->id,
        'debt_id' => $this->debt->id,
        'name' => 'test share',
        'amount' => 500,
        'sent' => 0,
        'seen' => 0,
    ]);

    $this->other_group_user = $this->group->groupUsers->reject(fn($group_user) 
        => $group_user->id === $this->group_user->id)->first();

    $this->actingAs($this->user);
});

/**
 * Tests for shared behaviours where the debt type is irrelevant
 * - Select 'sent' on own share
 * - Select 'seen' on own debt share that's not their share
 * - Can not select 'sent' on a share they don't own
 * - Can not select 'seen' on a share they don't own
 * - Can not select 'seen' on a share they own, but has not been sent
 * - Can not select 'seen' on a share they do own, for a debt they do not own
 */
test('user can select sent on their own share', function() {
    $response = $this->patch(route('share.sent', $this->share), [
        'id' => $this->share->id,
        'sent' => !$this->share->sent,
    ]);

    $response->assertStatus(302)->assertSessionHasNoErrors();

    $this->assertDatabaseHas('shares', [
        'id' => $this->share->id,
        'sent' => !$this->share->sent,
    ]);
});

test('user can select seen on a share they do not own for a debt they own', function() {
    $share = $this->debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

    $share->sent = 1;
    $share->save();

    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    $response->assertStatus(302)->assertSessionHasNoErrors();

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);
});

test('user can not select sent on a share they do not own', function() {
    $share = $this->debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

    $response = $this->patch(route('share.sent', $share), [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['sent' => "You do not have permission to update the 'sent' status of this share"]);

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'sent' => $share->sent,
    ]);
});

test('user can not select seen on a share they do not own', function() {
    $share = $this->debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['seen' => "You can not mark this share as seen becase it has not been sent yet"]);

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => $share->seen,
    ]);
});

test('user can not select seen on a share for a debt they own, but has not been sent', function() {
    $share = $this->debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

    $share->sent = 0;
    $share->save();

    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['seen' => "You can not mark this share as seen becase it has not been sent yet"]);

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => $share->seen,
    ]);
});

test('user can not select seen on a share they do own for a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = Share::factory()->create([
        'group_user_id' => $this->group_user->id,
        'debt_id' => $debt->id,
        'amount' => 500,
        'sent' => 1,
    ]);

    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['seen' => "You do not have permission to update the 'seen' status of this share"]);

    $this->assertDatabaseHas('shares', [
        'group_user_id' => $this->group_user->id,
        'debt_id' => $debt->id,
        'amount' => 500,
        'sent' => 1,
        'seen' => 0,
    ]);
});

/**
 * Tests for create/update/delete behaviours that are not sent/seen, type irrelevant:
 * - Update name on own share
 * - Not update on name on share they don't own and a debt they don't own
 * - Update name on a share they don't own, but a debt they do own
 * - Can not add a share without an amount
 * - Can not add a share to a debt they do not own
 * - Can not delete a share for a debt they do not own
 */

test('user can update the name of their own share', function() {
    $response = $this->patch(route('share.update', $this->share), [
        'id' => $this->share->id,
        'name' => 'i have been updated successfully',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Share updated successfully.');

    $this->assertDatabaseHas('shares', [
        'id' => $this->share->id,
        'name' => 'i have been updated successfully',
    ]);
});

test('user can not update a name of a share they do not own in a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'name' => 'i have been updated successfully',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors('share', 'You do not have permission to update this share.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'name' => $share->name,
    ]);
});

test('user can update the name of a share they do not own in a debt they do own', function() {
    $share = $this->debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'name' => 'i have been updated successfully',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Share updated successfully.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'name' => 'i have been updated successfully',
    ]);
});

test('user can not add a share without an amount', function() {
    $response = $this->post(route('share.store'), [
        'debt_id' => $this->debt->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'new share',
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors('amount', 'The amount field is required.');

    $this->assertDatabaseMissing('shares', [
        'debt_id' => $this->debt->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'new share',
    ]);
});

test('user can not add a share to a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 500,
        'name' => 'new share',
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors('debt_id', 'You do not have permission to add a share to this debt.');

    $this->assertDatabaseMissing('shares', [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_user->id,
        'name' => 'new share',
    ]);
});

test('user can not delete a share for a debt they do not own', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->reject(fn($share) =>
        $share->group_user_id === $this->group_user->id)->first();

    $response = $this->delete(route('share.destroy', $share));

    $response->assertStatus(302)
        ->assertSessionHasErrors('id', 'You do not have permission to delete this share.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'group_user_id' => $share->group_user_id,
        'debt_id' => $share->debt_id,
        'deleted_at' => null,
    ]);
});

/**
 * Tests for split even
 * - Can add split even debt share, does not recalc debt total
 * - Can not update a split even debt share amount at all
 * - Can delete split even debt share, does not recalc debt total
 */

test('user can add a share to a split even debt they own, and the balance is recalculated', function() {
    $debt = Debt::factory()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
    ]);

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_user->id,
        'amount' => $debt->shares->first()->amount->getMinorAmount()->toInt(),
        'name' => 'new split even share',
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share created successfully.')
        ->assertSessionHasNoErrors();

    $splits = Debt::findOrFail($debt->id)->amount
        ->split($debt->fresh()->shares->count());

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
    ]);

    foreach ($splits as $key => $split) {
        $this->assertDatabaseHas('shares', [
            'debt_id' => $debt->id,
            'group_user_id' => $this->group_user->id,
            'amount' => $split->getMinorAmount()->toInt(),
        ]);
    }
});

test('user can not update the amount on a split even debt share', function() {
    $debt = Debt::factory()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
    ]);

    $share = $debt->shares->where('group_user_id', $this->group_user->id)->first();

    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'amount' => $share->amount->getMinorAmount()->toInt() + 1000,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'amount' => 'You do not have permission to update the amount of this share.'
        ]);
    
        $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'amount' => $share->amount->getMinorAmount()->toInt(),
    ]);
});

test('user can delete a share in a split even debt they own and the debt total is unaffected', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'group_id' => $this->group->id,
        'split_even' => 1,
        'amount' => 1000,
    ]);

    $response = $this->delete(route('share.destroy', $debt->shares->last()));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share deleted successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'amount' => $debt->amount->getMinorAmount()->toInt(),
    ]);

    $this->assertDatabaseHas('shares', [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

/**
 * Tests for non-split (standard) debts
 * - Can add a share to a standard debt they own, balance is recalculated
 * - Can update the amount of a share of a standard debt they own, balance is recalculated
 * - Can delete a share of a standard debt they own, balance is recalculated
 */

test('user can add a share to a standard debt they own and the balance is recalcuated', function() {
    $response = $this->post(route('share.store'), [
        'debt_id' => $this->debt->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 700,
        'currency' => 'GBP',
        'name' => 'new share',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share created successfully.')
        ->assertSessionHasNoErrors();

    
    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'amount' => $this->debt->amount->plus(Money::of(7, 'GBP'))->getMinorAmount()->toInt(),
        'group_user_id' => $this->group_user->id,
    ]);

    $this->assertDatabaseHas('shares', [
        'debt_id' => $this->debt->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 700,
        'name' => 'new share',
    ]);
});

test('user can update the amount of a share of a standard debt they own and the balance is recalculated', function() {
    $response = $this->patch(route('share.update', $this->share), [
        'id' => $this->share->id,
        'amount' => 1000,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'amount' => $this->debt->amount->plus(Money::of(5, 'GBP'))->getMinorAmount()->toInt(),
        'group_user_id' => $this->group_user->id,
    ]);

    $this->assertDatabaseHas('shares', [
        'debt_id' => $this->debt->id,
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'name' => $this->share->name,
    ]);
});

test('user can delete a share of a standard debt they own and the balance is recalculated', function() {
    $response = $this->delete(route('share.destroy', $this->share));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share deleted successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'amount' => $this->debt->amount->minus(Money::of(5, 'GBP'))->getMinorAmount()->toInt(),
        'group_user_id' => $this->group_user->id,
    ]);

    $this->assertDatabaseHas('shares', [
        'id' => $this->share->id,
        'group_user_id' => $this->share->group_user_id,
        'debt_id' => $this->share->debt_id,
        'amount' => 500,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});


