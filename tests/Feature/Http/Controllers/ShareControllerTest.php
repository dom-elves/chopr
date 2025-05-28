<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use App\Events\ShareDeleted;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->get()[0];

    $this->actingAs($this->user);
});

test("user can select 'sent' on their own share", function () {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->where('user_id', $this->user->id)->first();
    
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();

    // confirm status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);
});

test("user can not select 'sent' on a share they do not own", function () {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    // get a share that's not mine
    $share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();

    // try to update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);

    // check correct error message is sent (only one for id)
    $response->assertSessionHasErrors('id');

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'sent' => $share->sent,
    ]);
});

test("user can select 'seen' on a share they don't own for a debt they own", function () {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    // get a share that's not mine
    $share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();
    
    // try to update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct response
    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);
});

test("user can not select 'seen' on the share for a debt they do not own", function () {
    // a share i don't own, in a debt i don't own
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();

   // try to update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct error message is sent (only one for id)
    $response->assertSessionHasErrors('id');

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'sent' => $share->sent,
    ]);
});

test("user can delete a share for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    // a share i don't own in a debt i do own
    $share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();
    
    // delete it
    $response = $this->delete(route('share.destroy'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share deleted successfully.')
        ->assertSessionHasNoErrors();;

    // confirm it's gone
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'amount' => $debt->amount - $share->amount,
    ]);
});

test("user can update the amount on a share for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $share = $debt->shares->where('user_id', $this->user->id)->first();

    $original_amount = $share->amount;

    // update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'amount' => 500,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'user_id' => $share->user_id,
        'amount' => 500,
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'amount' => $debt->amount - $original_amount + 500,
    ]);
});

test("user can update the name on a share for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $share = $debt->shares->where('user_id', $this->user->id)->first();

    // update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'name' => 'new name for this share'
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();;

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'user_id' => $share->user_id,
        'name' => 'new name for this share'
    ]);
});

test("user can not select 'seen' on a share they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $share = $debt->shares->where('user_id', $this->user->id)->first();
    
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct response
    $response->assertStatus(302)
        ->assertSessionHasErrors('id', 'You do not have permission to edit or delete this share.');

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);
});

test("user can add a share to a debt they are in", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'user_id' => $this->user->id,
        'amount' => 500,
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('shares', [
        'debt_id' => $debt->id,
        'user_id' => $this->user->id,
        'amount' => 500,
    ]);
});

/**
 * these are all tests for functionality that by default, are hidden from users behind js on the Controls component
 */
test("user can not delete a share for a debt they do not own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->first();

    $response = $this->delete(route('share.destroy'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
    ]);

    // check is against debt id
    $response->assertSessionHasErrors('debt_id', 'You do not have permission to update or deleted this share.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => null,
    ]);
});

test("user can not update the a amount on a share for a debt they do not own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);
    
    $share = $debt->shares->first();

    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'amount' => $debt->id,
    ]);

    // this time we're checking against share id
    // delete validation is all done in controller
    // whereas update is done in the Request class
    $response->assertSessionHasErrors('id', 'You do not have permission to update or deleted this share.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'amount' => $share->amount,
    ]);
});
