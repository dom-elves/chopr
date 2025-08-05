<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Invite;
use App\Models\GroupUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToGroup;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->get()[0];
});

test('user can invite someone to the group if they are the owner', function() {
    Mail::fake();
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
    
    Mail::assertSent(InviteToGroup::class, 'randomguy@example.com');
    Mail::assertSentCount(1);
});

test('user can invite multiple people to a group they own', function() {
    Mail::fake();
    $this->actingAs($this->user);

    for ($i =0; $i < 10; $i++) {
        $recipients[] = fake()->unique()->safeEmail();
    };

    $response = $this->post(route('invite.send'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipients' => $recipients,
        'body' => 'all of you join',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', count($recipients) . ' invites sent successfully.')
        ->assertSessionHasNoErrors();

    foreach ($recipients as $recipient) {
        $this->assertDatabaseHas('invites', [
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'recipient'=> $recipient,
            'body' => 'all of you join',
        ]);
    }

    Mail::assertSent(InviteToGroup::class, $recipients);
    Mail::assertSentCount(count($recipients));
});

test('user can not invite someone to the group if they are not the owner', function() {
    Mail::fake();
    $other_user = $this->group->users->reject(fn($user) =>
        $user->id === $this->user->id)->first();
  
    $this->actingAs($other_user);

    $response = $this->post(route('invite.send'), [
        'group_id' => $this->group->id,
        'user_id' => $other_user->id,
        'recipients' => ['dontaddme@example.com'],
        'body' => 'not you',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'group_id' => 'You do not have permission to edit or delete this group'
    ]);

    $this->assertDatabaseMissing('invites', [
        'group_id' => $this->group->id,
        'user_id' => $other_user->id,
        'recipient' => 'dontaddme@example.com',
        'body' => 'not you',
    ]);

    Mail::assertNothingSent();
});

test('user can not invite anyone without adding at least one email address', function() {
    Mail::fake();
    $this->actingAs($this->user);

    $response = $this->post(route('invite.send'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipients' => [],
        'body' => 'this is going to no one',
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'recipients' => 'Please enter one or more email addresses',
    ]);

    $this->assertDatabaseMissing('invites', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipient' => '',
        'body' => 'this is going to no one',
    ]);

    Mail::assertNothingSent();
});

test('registering as an invited new user creates a user and group user', function() {
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'group_id' => $invite->group_id,
        'token' => $invite->token,
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $user = User::where('name', 'Test User')->where('email', 'test@example.com')->first();

    $this->assertDatabaseHas('group_users', [
        'user_id' => $user->id,
        'group_id' => $invite->group_id,
    ]);

    $this->assertDatabaseHas('invites', [
        'id' => $invite->id,
        'accepted_at' => Carbon::now(),
    ]);
});

// actualyl test mails are correctly sent??

test('a group invite can be accepted for an existing user', function() {
    
});

test('a group invite can be accepted for a non-existant user', function() {

});

test('a user can not accept a group invite for a group they are already in', function() {

});