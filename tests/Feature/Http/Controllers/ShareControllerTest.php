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
    $this->debt = Debt::where('user_id', $this->user->id)->first();
    $this->shares = $this->debt->shares;

    $this->actingAs($this->user);
});

test("user can select 'sent' on their own share", function () {
    // get a share i own
    $share = $this->shares->where('user_id', $this->user->id)->first();

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
    // get a share that's not mine
    $share = $this->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();
    
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
        $debt->user_id === $this->user->id)->first();

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
    // a share i don't own in a debt i do own
    $share = $this->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();
    
    // delete it
    $response = $this->delete(route('share.destroy'), [
        'id' => $share->id,
        'debt_id' => $this->debt->id,
    ]);

    $response->assertStatus(200);

    // confirm it's gone
    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);

    $this->assertDatabaseHas('debts', [
        'id' => $this->debt->id,
        'amount' => $this->debt->amount - $share->amount,
    ]);
});

test("user can edit a share for a debt they own", function() {
    $share = $this->shares->reject(fn($share) => 
        $share->user_id === $this->user->id)->first();

    $original_amount = $share->amount;

    $debt = $share->debt;
    
    // update it
    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'amount' => 500,
    ]);

    $response->assertStatus(200);

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

test("user can not select 'seen' on a share they own", function() {
    $response = $this->patch(route('share.update'), [
        'id' => $this->user->shares->first()->id,
        'seen' => !$this->user->shares->first()->seen,
    ]);

    // check correct response
    $response->assertStatus(200);

    // confirm original status
    $this->assertDatabaseHas('shares', [
        'id' => $this->user->shares->first()->id,
        'seen' => !$this->user->shares->first()->seen,
    ]);
});

test("user can add a share to a debt they are in", function() {
    $response = $this->post(route('share.store'), [
        'debt_id' => $this->debt->id,
        'user_id' => $this->user->id,
        'amount' => 500,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('shares', [
        'debt_id' => $this->debt->id,
        'user_id' => $this->user->id,
        'amount' => 500,
    ]);
});

/**
 * these are all tests for functionality that by default, are hidden from users behind js on the Controls component
 */
test("user can not delete a share for a debt they do not own", function() {
    $debt = Debt::where('user_id', '!=', $this->user->id)->first();
    $share = $debt->shares->first();

    $response = $this->delete(route('share.destroy'), [
        'id' => $share->id,
        'debt_id' => $debt->id,
    ]);

    // check is against debt id
    $response->assertSessionHasErrors('debt_id');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => null,
    ]);
});

test("user can not update the a amount on a share for a debt they do not own", function() {
    $debt = Debt::where('user_id', '!=', $this->user->id)->first();
    $share = $debt->shares->first();

    $response = $this->patch(route('share.update'), [
        'id' => $share->id,
        'amount' => $debt->id,
    ]);

    // this time we're checking against share id
    // delete validation is all done in controller
    // whereas update is done in the Request class
    $response->assertSessionHasErrors('id');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'amount' => $share->amount,
    ]);
});
