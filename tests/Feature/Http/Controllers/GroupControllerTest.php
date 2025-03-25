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
    $group = Group::where('user_id', $this->user->id)->first();
    
    $response = $this->patch(route('group.update'), [
        'group_id' => $group->id,
        'name' => $group->name . '-edited',
        'user_id' => $this->user->id,
    ]);

    // todo: absolutely no idea why some inertia stuff is 302 but this is 200
    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name . '-edited',
        'user_id' => $this->user->id,
    ]);
});

test('user can not change the name of a group they do not own', function() {
    // seeder will always have a user with id 2
    $group = Group::factory()->create([
        'user_id' => 2,
    ]);
    
    $response = $this->patch(route('group.update'), [
        'group_id' => $group->id,
        'name' => $group->name . '-edited',
        'user_id' => 2,
    ]);

    $response->assertStatus(302);
    $response->assertInvalid([
        'user_id' => 'You do not have permission to edit or delete this group',
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name,
        'user_id' => 2,
    ]);
});

test('user can delete group they own', function() {
    // seeder always has me owning at least one group
    $group = Group::where('user_id', $this->user->id)->first();
    
    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name,
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name,
        'user_id' => $this->user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('deleting a group deletes the relevant group users', function() {
    $group = Group::where('user_id', $this->user->id)->first();
    $group_users = $group->group_users;

    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name,
        'user_id' => $this->user->id,
    ]);

    foreach ($group_users as $group_user) {
        $this->assertDatabaseHas('group_users', [
            'id' => $group_user->id,
            'group_id' => $group->id,
            'user_id' => $group_user->user_id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    } 
});

test('deleting a group deletes the relevant debts', function() {
    $group = Group::where('user_id', $this->user->id)->first();
    $debts = $group->debts;

    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name,
        'user_id' => $this->user->id,
    ]);

    foreach ($debts as $debt) {
        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'group_id' => $group->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    } 
});

test('deleting a group deletes the relevant shares', function() {
    $group = Group::where('user_id', $this->user->id)->first();
    $shares = $group->debts->first()->shares;

    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name,
        'user_id' => $this->user->id,
    ]);

    foreach ($shares as $share) {
        $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'debt_id' => $group->debts->first()->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    } 
});

test('user can not delete a group they do not own', function() {
    // seeder will always have a user with id 2
    $group = Group::factory()->create([
        'user_id' => 2,
    ]);
    
    $response = $this->delete(route('group.destroy'), [
        'group_id' => $group->id,
        'name' => $group->name,
        'user_id' => 2,
    ]);
    
    $response->assertStatus(302);
    $response->assertInvalid([
        'user_id' => 'You do not have permission to edit or delete this group',
    ]);
    
    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $group->name,
        'user_id' => 2,
        'deleted_at' => null,
    ]);
});

test('user can create a group and a group user for themselves is automatically created', function() {
    $response = $this->post(route('group.store'), [
        'name' => 'testgroup555',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'name' => 'testgroup555',
        'user_id' => $this->user->id,
    ]);

    $group = Group::where('name', 'testgroup555')
        ->where('user_id', $this->user->id)
        ->first();
    
    $this->assertDatabaseHas('group_users', [
        'user_id' => $this->user->id,
        'group_id' => $group->id,
    ]);
});