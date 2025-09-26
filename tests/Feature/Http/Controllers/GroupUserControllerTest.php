<?php

use App\Models\User;
Use App\Models\Group;

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

    $response = $this->delete(route('group-users.destroy'), [
        'group_id' => $this->group->id,
        'group_user_id' => $this->group->group_users[2]->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group User deleted successfully.');
});

test('user can not remove group users from a group they do not own', function() {
    $this->actingAs($this->user);

    $group = Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->users[2]->id,
    ]);
    
    $response = $this->delete(route('group-users.destroy'), [
        'group_id' => $group[0]->id,
        'group_user_id' => $group[0]->group_users[2]->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors('group_id', 'You do not have permission to edit or delete this group');
});
