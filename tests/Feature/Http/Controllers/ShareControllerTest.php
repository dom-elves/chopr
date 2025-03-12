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

    // since these are shares tests, debts will always be involved
    $this->debt = Debt::where('collector_group_user_id', $this->group_user->id)->first();
    $this->shares = $this->debt->shares;

    $this->actingAs($this->user);
});

test("user can select 'sent' on their own share", function () {
    // get a share i own
    $share = $this->shares->where('group_user_id', $this->group_user->id)->first();

    // update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);

    $response->assertStatus(200);

    // confirm status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'sent' => !$share->sent,
    ]);
});

test("user can not select 'sent' on a share they do not own", function () {
    // get a share that's not mine
    $share = $this->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

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

test("user can select 'seen' on the share for a debt they own", function () {
    // get a share that's not mine
    $share = $this->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();
    
    // try to update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);

    // check correct response
    $response->assertStatus(200);

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'seen' => !$share->seen,
    ]);
});

test("user can not select 'seen' on the share for a debt they do not own", function () {
    // a share i don't own, in a debt i don't own
    $debts = Debt::where('group_id', $this->group->id)->get();
    $debt = $debts->reject(fn($debt) => 
        $debt->collector_group_user_id === $this->group_user->id)->first();

    $share = $debt->shares->reject(fn($share) => 
        $share->group_user_id === $this->group_user->id)->first();

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
    // remember to recalculate shares & debt
});

test("user can edit a share for a debt they own", function() {
    // remember to recalcualte shares & debt
});

/**
 * these are all tests for functionality that by default, are hidden from users behind some js
 */
test("user can not delete a share for a debt they do not own", function() {

});

test("user can not edit a share for a debt they do not own", function() {

});

test("user can not select 'sent' on a share they own", function() {

});


test("user can not select 'seen' on a share they own", function() {

});