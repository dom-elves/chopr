<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Alias;

beforeEach(function () {
    $this->users = User::factory(10)->create();
    $this->user = $this->users[0];

    $this->group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->actingAs($this->user);
});

test('user can add an alias for another user', function() {
    $other_group_user = $this->group->groupUsers->reject(fn($group_user) => 
        $group_user->user_id === $this->user->id)->first();

    $response = $this->post(route('alias.store'), [
        'alias' => 'some alias',
        'user_id' => $this->user->id,
        'group_user_id' => $other_group_user->id,
    ]);
    
    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Alias created successfully.')
        ->assertRedirect('/groups');

    $this->assertDatabaseHas('aliases', [
        'alias' => 'some alias',
        'user_id' => $this->user->id,
        'group_user_id' => $other_group_user->id,
    ]);
});

test('user can update an alias for another user', function() {
    $other_group_user = $this->group->groupUsers->reject(fn($group_user) => 
        $group_user->user_id === $this->user->id)->first();
    
    $alias = Alias::create([
        'alias' => 'change me',
        'user_id' => $this->user->id,
        'group_user_id' => $other_group_user->id,
    ]);
    
    $response = $this->patch(route('alias.update', $alias->id), [
        'id' => $alias->id,
        'alias' => 'some new alias',
        'user_id' => $this->user->id,
        'group_user_id' => $other_group_user->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Alias updated successfully.')
        ->assertRedirect('/groups');
    
    $this->assertDatabaseHas('aliases', [
        'id' => $alias->id,
        'alias' => 'some new alias',
        'user_id' => $this->user->id,
        'group_user_id' => $other_group_user->id,
    ]);
});

