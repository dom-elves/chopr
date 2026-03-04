<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\Alias;
use App\Models\Comment;
use Carbon\Carbon;

beforeEach(function () {
   // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::first();
});

test('user can remove group users from a group they own', function() {
    $this->actingAs($this->user);

    $response = $this->delete(route('group-users.destroy', $this->group->group_users[2]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('group_users', [
        'id' => $this->group->group_users[2]->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not remove group users from a group they do not own', function() {
    $this->actingAs($this->user);

    $group = Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->users[2]->id,
    ]);

    $group_user = $group[0]->group_users->firstWhere('id', '!=', $this->user->id);

    $response = $this->delete(route('group-users.destroy', $group_user));

    $response->assertStatus(302)
        ->assertSessionHasErrors('id', 'You do not have permission to delete this group user.');

    $this->assertDatabaseHas('group_users', [
        'id' => $group_user->id,
        'deleted_at' => null,
    ]);
});

test('deleting a group user also delets their comments', function() {
    $this->actingAs($this->user);

    $debt = Debt::factory()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    // todo: change this after fixing debt factory later
    $comment = Comment::factory()->create([
        'group_user_id' => $this->group->group_users[0]->id,
        'debt_id' => $debt->id,
        'content' => 'comment'
    ]);

    $response = $this->delete(route('group-users.destroy', $this->group->group_users[0]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $this->group->group_users[0]->id,
    ]);
});

test('deleting a group user also delets their shares', function() {
    $this->actingAs($this->user);

    $debt = Debt::factory()->withShares()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    $share = $debt->shares->where('group_user_id', $this->group->group_users[0]->id)->first();

    $response = $this->delete(route('group-users.destroy', $this->group->group_users[0]));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $share->group_user_id,
    ]);
});

test('deleting a group user also delets their aliases', function() {
    $this->actingAs($this->user);

    $group_user = $this->group->group_users->firstWhere('id', '!=', $this->user->id);

    $alias = Alias::factory()->create([
        'user_id' => $this->user->id,
        'group_user_id' => $group_user->id,
    ]);

    $response = $this->delete(route('group-users.destroy', $group_user));

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');

    $this->assertDatabaseHas('aliases', [
        'id' => $alias->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $alias->group_user_id,
    ]);
});
