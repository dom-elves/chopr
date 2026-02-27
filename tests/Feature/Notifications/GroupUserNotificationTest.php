<?php

use Illuminate\Support\Facades\Notification;
use App\Notifications\GroupUserCreatedNotification;
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;

beforeEach(function () {
    // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    $this->actingAs($this->user);

    Notification::fake();
});

test('creating a group sends a notification to the creator', function() {
    $response = $this->post(route('group.store'), [
        'name' => 'test group',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Group created successfully.')
        ->assertRedirect('/groups');

    Notification::assertSentTo($this->user, GroupUserCreatedNotification::class);
});

test('a user joining a group sends a notification to user', function() {
    // normally this would be done by a user accepting an invite
    // but that's already covered in invite tests
    $group = Group::factory()->create([
        'user_id' => $this->user->id,
    ]);

    GroupUser::factory()->create([
        'user_id' => $this->users[1]->id,
        'group_id' => $group->id,
    ]);

    Notification::assertSentTo($this->users[1], GroupUserCreatedNotification::class);
});