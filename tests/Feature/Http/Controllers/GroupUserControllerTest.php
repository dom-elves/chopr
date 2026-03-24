<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Alias;
use App\Models\Comment;
use Carbon\Carbon;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(10)->create();
    $this->user = $this->users[0];

    $this->group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->group_user = $this->group->groupUsers->where('user_id', $this->user->id)->first();
});

test('user can remove group users from a group they own', function() {
    $this->actingAs($this->user);

    $response = $this->delete(route('group-users.destroy', $this->group->groupUsers[2]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('group_users', [
        'id' => $this->group->groupUsers[2]->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not remove group users from a group they do not own', function() {
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

test('deleting a group user also deletes their comments', function() {
    $this->actingAs($this->user);

    $debt = Debt::factory()->create([
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
    ]);

    // todo: change this after fixing debt factory later
    $comment = Comment::factory()->create([
        'group_user_id' => $this->group->groupUsers[2]->id,
        'debt_id' => $debt->id,
        'content' => 'comment'
    ]);

    $response = $this->delete(route('group-users.destroy', $this->group->groupUsers[2]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $this->group->groupUsers[2]->id,
    ]);
});

test('deleting a group user also deletes their shares', function() {
    $this->actingAs($this->user);

    $debt = Debt::factory()->withShares()->create([
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
    ]);

    $share = $debt->shares->where('group_user_id', $this->group->groupUsers[2]->id)->first();

    $response = $this->delete(route('group-users.destroy', $this->group->groupUsers[2]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $share->group_user_id,
    ]);
});

test('deleting a group user also deletes their aliases', function() {
    $this->actingAs($this->user);

    $alias = Alias::factory()->create([
        'user_id' => $this->user->id,
        'group_user_id' => $this->group->groupUsers[2]->id,
    ]);

    $response = $this->delete(route('group-users.destroy', $this->group->groupUsers[2]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('aliases', [
        'id' => $alias->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $this->group->groupUsers[2]->id,
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
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('group_users', [
        'id' => $this->group_user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group_user->group->id,
        'user_id' => $this->group->groupUsers[2]->user->id,
    ]);
});
