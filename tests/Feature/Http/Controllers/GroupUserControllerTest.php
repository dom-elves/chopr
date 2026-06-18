<?php

use App\Models\User;
Use App\Models\Group;
use Carbon\Carbon;

beforeEach(function () {
    $this->users = User::factory(10)->create();
    $this->user = $this->users[0];

    $this->group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->group_user = $this->group->groupUsers->where('user_id', $this->user->id)->first();
});

test('user can delete group users from a group they own', function() {
    $this->actingAs($this->user);

    $response = $this->delete(route('group-users.destroy', $this->group->groupUsers[2]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully. Your balance may take a moment to update.');

    $this->assertDatabaseHas('group_users', [
        'id' => $this->group->groupUsers[2]->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not delete group users from a group they do not own', function() {
    $this->actingAs($this->user);

    $group = Group::factory()
        ->create([
            'user_id' => $this->users[2]->id,
        ]);

    $group_user = $group->groupUsers->firstWhere('id', '!=', $this->user->id);

    $response = $this->delete(route('group-users.destroy', $group_user));

    $response->assertStatus(302)
        ->assertSessionHasErrors('id', 'You do not have permission to delete this group user.');

    $this->assertDatabaseHas('group_users', [
        'id' => $group_user->id,
        'deleted_at' => null,
    ]);
});

test('user can not delete themselves from a group they own without selecting a new owner', function() {
    $this->actingAs($this->user);

    $response = $this->delete(route('group-users.destroy', $this->group_user));

    $response->assertStatus(302)
        ->assertSessionHasErrors('new_owner_group_user_id', 'Please select a new group owner before leaving the group');

    $this->assertDatabaseHas('group_users', [
        'id' => $this->group_user->id,
        'deleted_at' => null,
    ]);
});

test('user can delete themselves from a group and select a new group owner', function() {
    $this->actingAs($this->user);

    $response = $this->delete(route('group-users.destroy', $this->group_user), [
        'new_owner_group_user_id' => $this->group->groupUsers[2]->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully. Your balance may take a moment to update.');

    $this->assertDatabaseHas('group_users', [
        'id' => $this->group_user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group_user->group->id,
        'user_id' => $this->group->groupUsers[2]->user->id,
    ]);
});

// tests around extended functionality for deleting group users will be a separate test case
