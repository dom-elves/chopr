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
use Illuminate\Support\Facades\Queue;
use App\Jobs\ExpireInvite;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(10)->create();
    $this->user = $this->users[0];

    Group::factory()
        ->withGroupUsers(5)
        ->create([
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

    Mail::assertQueued(InviteToGroup::class, 'randomguy@example.com');
    Mail::assertQueuedCount(1);

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

test('user can invite someone to the group if they are not the owner', function() {
    Mail::fake();
    $not_owner = $this->users->reject(fn($user) => $user->id !== $this->user->id)->first();
    $this->actingAs($not_owner);

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
    
    Mail::assertQueued(InviteToGroup::class, 'randomguy@example.com');
    Mail::assertQueuedCount(1);
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

    Mail::assertQueued(InviteToGroup::class, $recipients);
    Mail::assertQueuedCount(count($recipients));
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
            'recipients' => 'Please enter one or more email addresses.',
    ]);

    $this->assertDatabaseMissing('invites', [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipient' => '',
        'body' => 'this is going to no one',
    ]);

    Mail::assertNothingQueued();
});

test('invite accept link renders the register component if the user does not exist', function() {
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        "accepted_at" => null,
        "expired_at" => null,
        "deleted_at" => null,
    ]);

    $link = URL::temporarySignedRoute(
                    'invite.accept', now()->plus(minutes: 1), ['invite' => $invite]
                );
    
    $response = $this->get($link);

    $response->assertInertia(function (AssertableInertia $page) use ($invite) {
        $page->component('Auth/Register')
                ->where('invite', $invite);
    });
});

test('registering as an invited new user creates a user and group user', function() {
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    session(['invite' => $invite]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('group.index'))
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

    $link = URL::temporarySignedRoute(
                    'invite.accept', now()->plus(minutes: 1), ['invite' => $invite]
                );

    $response = $this->get($link);

    $this->assertDatabaseHas('invites', [
        'id' => $invite->id,
        'accepted_at' => Carbon::now(),
    ]);

    $this->assertDatabaseHas('group_users', [
        'user_id' => $user->id,
        'group_id' => $this->group->id,
    ]);

    $response->assertRedirect(route('group.index'))
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
            'existing' => "The following recipients are already in the group: {$recipient->email}"
        ]);
});

test('user clicking on the invite link after accepting it logs them in and redirects them to groups', function() {
    $user = $this->group->users->first();

    $group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);
    
    $invite = Invite::factory()->create([
        'group_id' => $group->id,
        'user_id' => $this->user->id,
        'recipient' => $user->email,
        'accepted_at' => Carbon::now(),
    ]);

    $this->actingAs($user);

    $link = URL::temporarySignedRoute(
                    'invite.accept', now()->plus(minutes: 1), ['invite' => $invite]
                );

    $response = $this->get($link);

    $this->assertDatabaseHas('invites', [
        'id' => $invite->id,
        'accepted_at' => Carbon::now(),
    ]);
    
    $response->assertRedirect(route('group.index'))
        ->assertSessionHas('status', "You have successfully joined {$group->name}");
});

test('invites are expired after 24 hours', function() {
    Queue::fake();

    // don't need to bother going through the whole process
    // just create an invite and dispatch the job
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    ExpireInvite::dispatch($invite)->delay(Carbon::now()->addDays(1));

    // 2s allowance for micro differences that were failing the test
    Queue::assertPushed(ExpireInvite::class, function ($job) {
        return $job->delay->diffInSeconds(Carbon::now()->addDay()) < 2;
    });
});

test('user can not invite an address who has a pending invite for that group', function() {
    $invite = Invite::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipient' => 'dupeguy@example.com',
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('invite.send'), [
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
        'recipients' => ['dupeguy@example.com'],
        'body' => 'this person is already in the group',
    ]);
    
    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'pending' => "The following recipients have pending invites: dupeguy@example.com"
    ]);
});

// more logic for invalidating pending invites

