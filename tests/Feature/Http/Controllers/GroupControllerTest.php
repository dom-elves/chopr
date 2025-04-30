<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // create a handful of users so those involved can be randomised
    $this->users = User::factory(5)->create();
    $this->user = $this->users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->first();

    $this->actingAs($this->user);
});

// todo: write an expansed version of this for debts on /dashboard
test('user groups appear', function() {
    $this->get('/groups')
        ->assertInertia(fn (Assert $page) => 
            $page->component('Groups')
                ->has('groups', $this->user->groups->count())
        );
});

test('user can change the name of a group they own', function() {
    $response = $this->patch(route('group.update'), [
        'id' => $this->group->id,
        'name' => $this->group->name . '-edited',
        'user_id' => $this->user->id,
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name . '-edited',
        'user_id' => $this->user->id,
    ]);
});

test('user can not change the name of a group they do not own', function() {
    $this->actingAs($this->users->last());

    $response = $this->patch(route('group.update'), [
        'id' => $this->group->id,
        'name' => $this->group->name . '-edited',
        'user_id' => $this->users->last()->id,
    ]);

    $response->assertInvalid([
        'id' => 'You do not have permission to edit or delete this group',
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name,
    ]);
});

test('user can delete group they own', function() {
    $response = $this->delete(route('group.destroy'), [
        'id' => $this->group->id,
        'name' => $this->group->name,
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name,
        'user_id' => $this->user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('deleting a group deletes the relevant group users', function() {
    $group = Group::where('user_id', $this->user->id)->first();
    $group_users = $group->group_users;

    $response = $this->delete(route('group.destroy'), [
        'id' => $group->id,
        'name' => $group->name,
        'user_id' => $this->user->id,
    ]);

    foreach ($group_users as $group_user) {
        $this->assertDatabaseHas('group_users', [
            'id' => $group_user->id,
            'group_id' => $group->id,
            'user_id' => $group_user->user->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    } 
});

test('deleting a group deletes the relevant debts', function() {
    $debts = Debt::factory(5)->withShares()->create([
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->delete(route('group.destroy'), [
        'id' => $this->group->id,
        'name' => $this->group->name,
        'user_id' => $this->user->id,
    ]);

    foreach ($debts as $debt) {
        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'group_id' => $this->group->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    } 
});

test('deleting a group deletes the relevant shares', function() {
    $group = Group::where('user_id', $this->user->id)->first();
    $shares = $group->debts->first()->shares;

    $response = $this->delete(route('group.destroy'), [
        'id' => $group->id,
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
    $not_group_owner = $this->users->last();
    $this->actingAs($not_group_owner);
    
    $response = $this->delete(route('group.destroy'), [
        'id' => $this->group->id,
        'name' => $this->group->name,
        'user_id' => $not_group_owner->id,
    ]);
    
    $response->assertStatus(302);
    $response->assertInvalid([
        'id' => 'You do not have permission to edit or delete this group',
    ]);
    
    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name,
        'user_id' => $this->user->id,
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