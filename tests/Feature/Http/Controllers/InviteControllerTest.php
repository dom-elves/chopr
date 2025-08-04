<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\GroupUser;
use Carbon\Carbon;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->get()[0];

    // $this->actingAs($this->user);
});

test('user can invite someone to the group if they are the owner', function() {
    $this->actingAs($this->user);

    $response = $this->post(route('invite.send'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipients' => ['randomguy@example.com'],
        'body' => 'hey join this group',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', '1 invite sent successfully.')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('invites', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipient'=> 'randomguy@example.com',
        'body' => 'hey join this group',
    ]);
});

test('user can invite multiple people to a group they own', function() {

});

test('user can not invite someone to the group if they are not the owner', function() {

});

test('a group invite can be accepted, with a user & group user created', function() {

});