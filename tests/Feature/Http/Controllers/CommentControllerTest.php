<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Comment;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    User::factory(10)->create();
    $this->user = User::first();

    $this->group = Group::factory()
        ->hasGroupUsers(5)
        ->create([
            'user_id' => $this->user->id,
        ]);

    // the group user of the user that will be commenting etc
    $this->group_user = $this->user->group_users->where('group_id', $this->group->id)->first();

    // a debt belonging to one of the users
    $this->debt = Debt::factory()->withShares()->create([
        'group_id' => $this->group->id,
        'group_user_id' => $this->group_user->id,
    ]);

    $this->actingAs($this->user);
});

test('user can comment on a debt', function () {
    $response = $this->post(route('comment.store'), [
        'debt_id' => $this->debt->id,
        'content' => 'This is a comment',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Comment added successfully.');

    $this->assertDatabaseHas('comments', [
        'debt_id' => $this->debt->id,
        'content' => 'This is a comment',
        'group_user_id' => $this->group_user->id,
    ]);
});

test('user can not post an empty comment', function () {
    $response = $this->post(route('comment.store'), [
        'debt_id' => $this->debt->id,
        'content' => '',
        'group_user_id' => $this->group_user->id,
    ]);

    $response->assertSessionHasErrors(['content' => 'The content field is required.']);

    $this->assertDatabaseMissing('comments', [
        'content' => '',
        'group_user_id' => $this->group_user->id,
    ]);
});

test('user can edit their comment on a debt', function () {
    $comment = Comment::create([
        'group_user_id' => $this->group_user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    $response = $this->patch(route('comment.update', $comment), [
        'content' => 'I have now been updated',
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Comment updated successfully.');

    $this->assertDatabaseHas('comments', [
        'content' => 'I have now been updated',
        'edited' => 1,
        'group_user_id' => $this->group_user->id,
    ]);
});

test('user can delete their comment on a debt', function () {
    $comment = Comment::create([
        'group_user_id' => $this->group_user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    $response = $this->delete(route('comment.destroy', $comment));

    $response->assertStatus(302);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'group_user_id' => $this->group_user->id,
    ]);
});

test('user can not edit another user comment on a debt', function () {
    // get a different user & create a comment by them
    $other_group_user = GroupUser::where('id', '!=', $this->group_user->id)->first();
    $other_user_comment = Comment::create([
        'group_user_id' => $other_group_user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    // try and edit it
    $response = $this->patch(route('comment.update', $other_user_comment), [
        'content' => 'updated comment again',
    ]);

    // assert the correct error is in the response
    $response->assertSessionHasErrors([
        'content' => 'You do not have permission to edit this comment.',
    ]);

    // and then assert that the comment content remains the same
    $this->assertDatabaseHas('comments', [
        'content' => 'I am a comment on a debt',
        'edited' => null,
        'group_user_id' => $other_group_user->id,
    ]);
});

// same principle as above test, just slightly different assertions 
test('user can not delete another user comment on a debt', function () {
    $other_group_user = GroupUser::where('id', '!=', $this->group_user->id)->first();
    $other_user_comment = Comment::create([
        'group_user_id' => $other_group_user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    $response = $this->delete(route('comment.destroy', $other_user_comment));

    $response->assertSessionHasErrors([
        'id' => 'You do not have permission to delete this comment',
    ]);

    $this->assertDatabaseHas('comments', [
        'id' => $other_user_comment->id,
        'deleted_at' => null,
        'group_user_id' => $other_group_user->id,
    ]);
});

test('user can not comment on a debt they are not involved in', function () {
    $other_user = User::factory()->create();

    $this->actingAs($other_user);

    $response = $this->post(route('comment.store'), [
            'debt_id' => $this->debt->id,
            'content' => 'This is a comment',
        ]);

    $response->assertSessionHasErrors([
        'debt_id' => 'You do not have permission to comment on this debt.',
    ]);
});