<?php

use App\Models\User;
Use App\Models\Group;
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
});

test('user can remove group users from a group they own', function() {
    $this->actingAs($this->user);

    $response = $this->delete(route('group-users.destroy', $this->group->group_users[2]), [
        'id' => $this->group->group_users[2]->id,
    ]);

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

    $response = $this->delete(route('group-users.destroy', $group_user), [
        'id' => $group_user->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors('id', 'You do not have permission to delete this group user.');

    $this->assertDatabaseHas('group_users', [
        'id' => $group_user->id,
        'deleted_at' => null,
    ]);
});
