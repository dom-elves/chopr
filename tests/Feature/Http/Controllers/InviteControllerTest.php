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
use Inertia\Testing\AssertableInertia;

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

test('invite accept link renders the register component if the user does not exist', function() {
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);
    
    $response = $this->get('/invite/accept/' . $invite->token);

    $response->assertInertia(function (AssertableInertia $page) use ($invite) {
        $page->component('Auth/Register')
                ->where('invite.token', $invite->token);
    });
});

test('registering as an invited new user creates a user and group user', function() {
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    session(['token' => $invite->token]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', "You have successfully joined {$this->group->name}");

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

test("invite accept link creates a group user if the user does exist", function() {
    $user = User::factory()->create([
        'name' => 'Join ThisGroup',
        'email' => 'jointhisgroup@example.com',
    ]);

    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipient' => $user->email,
    ]);

    $this->actingAs($user);

    $response = $this->get('/invite/accept/' . $invite->token);

    $this->assertDatabaseHas('invites', [
        'id' => $invite->id,
        'accepted_at' => Carbon::now(),
    ]);

    $this->assertDatabaseHas('group_users', [
        'user_id' => $user->id,
        'group_id' => $this->group->id,
    ]);

    $response->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', "You have successfully joined {$this->group->name}");
});

test('a user can not send an invite link to a user who is already in the group', function() {
    $sender = $this->group->user;
    $recipient = $this->group->users->last();
    
    $this->actingAs($sender);
    
    $response = $this->post(route('invite.send'), [
        'group_id' => $this->group->id,
        'user_id' => $sender->id,
        'recipients' => [$recipient->email],
        'body' => 'this person is already in the group',
    ]);
    
    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'recipients.0' => "The user with email {$recipient->email} is already a member of the group."
        ]);
});

test('a user can not accept a group invite for a group they are already in', function() {
    $user = $this->group->users->first();

    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipient' => $user->email,
    ]);

    $this->actingAs($user);

    $response = $this->get('/invite/accept/' . $invite->token);
    
    $response->assertStatus(302)
        ->assertSessionHas('status', "You are already a member of this group.");
});

// logic for not being able to send an invite to user in group
// checks so they can't accept it anyway
// invite expiry