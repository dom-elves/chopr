<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Debt;
use Inertia\Testing\AssertableInertia as Assert;
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

    $this->actingAs($this->user);
});

test('user groups, users, group users appear with permissions and are paginated', function() {
    Group::factory()
        ->count(10)
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->get('/groups')
        ->assertInertia(fn (Assert $page) =>
            $page->component('Groups')
                ->has('groups.data', 5)
                ->has('groups.data.0.can', fn (Assert $can) => $can
                    ->has('update')
                    ->has('invite')
                    ->has('delete')
                )
                ->has('groups.data.0.group_users.0.can', fn (Assert $can) => $can
                    ->has('update')
                    ->has('delete')
                )
                ->has('groups.data.0.group_users.0.user')
                ->has('groups.data.0.group_users.0.aliases')
            );
});

test('user can create groups', function() {
    $response = $this->post(route('group.store'), [
        'name' => 'testgroup555',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group created successfully.');

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

test('user can change the name of a group they own', function() {
    $response = $this->patch(route('group.update', $this->group), [
        'id' => $this->group->id,
        'name' => $this->group->name . '-edited',
        'user_id' => $this->user->id,
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name . '-edited',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group updated successfully.');
});

test('user can not change the name of a group they do not own', function() {
    $this->actingAs($this->users->last());

    $response = $this->patch(route('group.update', $this->group), [
        'id' => $this->group->id,
        'name' => $this->group->name . '-edited',
        'user_id' => $this->users->last()->id,
    ]);

    $response->assertInvalid([
        'name' => 'You do not have permission to edit this group.',
    ]);

    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name,
    ]);
});

test('user can delete group they own', function() {
    $response = $this->delete(route('group.destroy', $this->group));

    $response->assertStatus(302)
        ->assertSessionHas('status', "Group and {$this->group->debts->count()} debts deleted successfully.");

    $this->assertDatabaseHas('groups', [
        'id' => $this->group->id,
        'name' => $this->group->name,
        'user_id' => $this->user->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('deleting a group deletes the relevant group users, debts, shares and comments', function() {
    $group = Group::where('user_id', $this->user->id)->first();
    $group_users = $group->groupUsers;
    
    $debts = Debt::factory(5)->withShares()->withComments()->create([
        'group_user_id' => $group_users[0]->id,
        'group_id' => $group->id,
    ]);

    $response = $this->delete(route('group.destroy', $this->group));

    $response->assertStatus(302)
        ->assertSessionHas('status', "Group and {$debts->count()} debts deleted successfully.");

    foreach ($group_users as $group_user) {
        $this->assertDatabaseHas('group_users', [
            'id' => $group_user->id,
            'group_id' => $group->id,
            'user_id' => $group_user->user->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
    
    foreach ($debts as $debt) {
        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'group_id' => $this->group->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        foreach ($debt->shares as $share) {
             $this->assertDatabaseHas('shares', [
            'id' => $share->id,
            'debt_id' => $group->debts->first()->id,
            'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        foreach ($debt->comments as $comment) {
            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'debt_id' => $group->debts->first()->id,
                'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
});

test('user can not delete a group they do not own', function() {
    $not_group_owner = $this->users->last();
    $this->actingAs($not_group_owner);
    
    $response = $this->delete(route('group.destroy', $this->group));
    
    $response->assertStatus(302);
    $response->assertInvalid([
        'id' => 'You do not have permission to delete this group',
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

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Group created successfully.');

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