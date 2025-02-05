<?php

use App\Models\User;
use App\Models\Group;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // Reset the database
    $this->artisan('migrate:fresh --seed');

    // seeder is built so i'm first user & at least in multiple groups with debts etc
    $this->user = User::first();

    $this->actingAs($this->user);
});

test('user groups appear', function() {
    $this->get('/groups')
        ->assertInertia(fn (Assert $page) => 
            $page->component('Groups')
                ->has('groups', $this->user->groups->count())
        );
});

test('user can change the name of a group they own', function() {
    // seeder always has me owning at least one group
    $group = Group::where('owner_id', $this->user->id)->first();
    
    $response = $this->patch(route('group.update'), [
        'group_id' => $group->id,
        'name' => $group->name . '-edited',
        'owner_id' => $this->user->id,
    ]);

    // todo: absolutely no idea why some inertia stuff is 302 but this is 200
    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name . '-edited',
        'owner_id' => $this->user->id,
    ]);
});

test('user can not change the name of a group they do not own', function() {
    // seeder will always have a user with id 2
    $group = Group::factory()->create([
        'owner_id' => 2,
    ]);
    
    $response = $this->patch(route('group.update'), [
        'group_id' => $group->id,
        'name' => $group->name . '-edited',
        'owner_id' => 2,
    ]);

    $response->assertStatus(302);
    $response->assertInvalid([
        'owner_id' => 'You do not have permission to edit or delete this group',
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name,
        'owner_id' => 2,
    ]);
});

test('user can delete group they own', function() {
    // seeder always has me owning at least one group
    $group = Group::where('owner_id', $this->user->id)->first();
    
    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name,
        'owner_id' => $this->user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name,
        'owner_id' => $this->user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not delete a group they do not own', function() {
    // seeder will always have a user with id 2
    $group = Group::factory()->create([
        'owner_id' => 2,
    ]);
    
    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name . '-edited',
        'owner_id' => 2,
    ]);

    $response->assertStatus(302);
    $response->assertInvalid([
        'owner_id' => 'You do not have permission to edit or delete this group',
    ]);
    
    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name,
        'owner_id' => 2,
    ]);
});

test('user can create a group and a group user for themselves is automatically created', function() {
    $response = $this->post(route('group.store'), [
        'group_name' => 'testgroup555',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'name' => 'testgroup555',
        'owner_id' => $this->user->id,
    ]);

    $group = Group::where('name', 'testgroup555')
        ->where('owner_id', $this->user->id)
        ->first();
    
    $this->assertDatabaseHas('group_users', [
        'user_id' => $this->user->id,
        'group_id' => $group->id,
    ]);
});