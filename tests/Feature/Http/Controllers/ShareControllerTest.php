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
use Brick\Money\Money;
use Brick\Math\RoundingMode;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->first();
    $this->group_users = $this->group->group_users;

    $this->actingAs($this->user);
});

test("user can select 'sent' on their own share", function () {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->where('group_user_id', $this->group_users[0]->id)->first();
    
    $response = $this->patch(route('share.sent', $share), [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);

    $response->assertStatus(302)->assertSessionHasNoErrors();

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
        $share->group_user_id === $this->group_users[0]->id)->first();

    // try to update it
    $response = $this->patch(route('share.sent', $share), [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['sent' => "You do not have permission to update the 'sent' status of this share"]);

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

    // set it to not sent
    $share->sent = 1;
    $share->save();

    // try to update it
    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct response
    $response->assertStatus(302)->assertSessionHasNoErrors();

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
    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['seen' => "You do not have permission to update the 'seen' status of this share"]);
    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'sent' => $share->sent,
    ]);
});

test("user can not select 'seen' on a share that has not yet been sent", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    // get a share that's not mine
    $share = $debt->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();

    // set it to not sent
    $share->sent = 0;
    $share->save();

    // try to update it
    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct response
    $response->assertStatus(302)
        ->assertSessionHasErrors(['seen' => "You can not mark this share as seen becase it has not been sent yet"]);

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => $share->seen,
    ]);
});

test("user can not select 'seen' on a share they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->first();

    $response = $this->patch(route('share.seen', $share), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct response
    $response->assertStatus(302)
        ->assertSessionHasErrors(['seen' => "You do not have permission to update the 'seen' status of this share"]);

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => $share->seen,
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
    $response = $this->delete(route('share.destroy', $share));

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
        'amount' => ($debt->amount->minus($share->amount))->getMinorAmount()->toInt(),
    ]);
});

test("user can update the name on a share for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);
    
    $share = $debt->shares->where('group_user_id', $this->group_users[0]->id)->first();

    // update it
    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'debt_id' => $debt->id,
        'name' => 'new name for this share',
        'amount' => $share->amount->getMinorAmount()->toInt(),
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'group_user_id' => $share->group_user_id,
        'name' => 'new name for this share'
    ]);
});

test("user can update the amount on a share for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);
    
    $share = $debt->shares->where('group_user_id', $this->group_users[0]->id)->first();

    // the 'new' amount is basically what the user sees,
    // e..g if they add a fiver it's just 5
    $new = $share->amount->plus(Money::of(10, 'GBP'));

    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'name' => $share->name,
        'debt_id' => $debt->id,
        // we have to do this weird thing as brick money deals in minor units
        // and does not make ints into decimals, only decimals into ints
        // so if new is 5.65, minorAmount gives us 565
        // then /100 to simulate user input of 5.65
        // as the casting is handled in Cash.php attribute cast
        'amount' => $new->getMinorAmount()->toInt() / 100
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share updated successfully.')
        ->assertSessionHasNoErrors();


    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'group_user_id' => $share->group_user_id,
        'amount' => $new->getMinorAmount()->toInt(),
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $debt->id,
        'amount' => $debt->amount->plus(Money::of(10, 'GBP'))->getMinorAmount()->toInt(),
    ]);
});

test("user can add a share for themselves for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_users[0]->id,
        'amount' => 500,
        'name' => 'new share',
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share created successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('shares', [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_users[0]->id,
        'amount' => 500 * 100,
    ]);
});

test("user can add a share for another user for a debt they own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'split_even' => 0,
    ]);

    $other_group_user = $debt->group_users->reject(fn($group_user) => 
        $group_user->id === $this->group_users[0]->id)->first();

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $other_group_user->id,
        'amount' => 750,
        'name' => 'new share for other user',
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Share created successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('shares', [
        'debt_id' => $debt->id,
        'group_user_id' => $other_group_user->id,
        'amount' => 750 * 100,
    ]);
});


/**
 * these are all tests for functionality that by default, 
 * are hidden from users behind js on the Controls component
 */

test("user can not add a share for a debt they do not own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->post(route('share.store'), [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_users[0]->id,
        'amount' => 500,
        'name' => 'new share',
        'currency' => 'GBP',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors('debt_id', 'You do not have permission to add a share to this debt.');

    $this->assertDatabaseMissing('shares', [
        'debt_id' => $debt->id,
        'group_user_id' => $this->group_users[0]->id,
        'amount' => 500,
    ]);
});


test("user can not delete a share for a debt they do not own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);

    $share = $debt->shares->first();

    $response = $this->delete(route('share.destroy', $share));

    // check is against debt id
    $response->assertSessionHasErrors('id', 'You do not have permission to delete this share.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => null,
    ]);
});

test("user can not update the name or amount on a share for a debt they do not own", function() {
    $debt = Debt::factory()->withShares()->create([
        'user_id' => $this->users->last()->id,
        'group_id' => $this->group->id,
    ]);
    
    $share = $debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_users[0]->id)->first();
 
    $new = $share->amount->plus(Money::of(10, 'GBP'));

    $response = $this->patch(route('share.update', $share), [
        'id' => $share->id,
        'debt_id' => $debt->id,
        'amount' => $new->getMinorAmount()->toInt(),
        'name' => 'new share name'
    ]);
    
    $response->assertSessionHasErrors('share', 'You do not have permission to update this share.');
    
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'amount' => $share->amount->getMinorAmount()->toInt(),
    ]);
});
