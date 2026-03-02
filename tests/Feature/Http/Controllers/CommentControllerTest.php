<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Comment;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // create a couple of users
    $users = User::factory(2)->create();
    $this->user = $users[0];

    // a group for them to go in
    Group::factory(1)->withGroupUsers()->create([
        'user_id' => $this->user->id,
    ]);

    $this->group = Group::where('user_id', $this->user->id)->first();

    // a debt belonging to one of the users
    $this->debt = Debt::factory()->withShares()->create([
        'group_id' => $this->group->id,
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);
});

test('user can comment on a debt', function () {
    $response = $this->post(route('comment.store'), [
        'debt_id' => $this->debt->id,
        'content' => 'This is a comment',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(302)
        ->assertSessionHas('status', 'Comment added successfully.');

    $this->assertDatabaseHas('comments', [
        'debt_id' => $this->debt->id,
        'content' => 'This is a comment',
        'user_id' => $this->user->id,
    ]);
});

test('user can not post an empty comment', function () {
    $response = $this->post(route('comment.store'), [
        'debt_id' => $this->debt->id,
        'content' => '',
        'user_id' => $this->user->id,
    ]);

    $response->assertSessionHasErrors(['content' => 'The content field is required.']);

    $this->assertDatabaseMissing('comments', [
        'content' => '',
    ]);
});

test('user can edit their comment on a debt', function () {
    $comment = Comment::create([
        'user_id' => $this->user->id,
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
    ]);
});

test('user can delete their comment on a debt', function () {
    $comment = Comment::create([
        'user_id' => $this->user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    $response = $this->delete(route('comment.destroy', $comment));

    $response->assertStatus(302);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not edit another user comment on a debt', function () {
    // get a different user & create a comment by them
    $other_user = User::where('id', '!=', $this->user->id)->first();
    $other_user_comment = Comment::create([
        'user_id' => $other_user->id,
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
    ]);
});

// same principle as above test, just slightly different assertions 
test('user can not delete another user comment on a debt', function () {
    $other_user = User::where('id', '!=', $this->user->id)->first();
    $other_user_comment = Comment::create([
        'user_id' => $other_user->id,
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
    ]);
});

test('user can not comment on a debt they are not involved in', function () {
    $other_user = User::factory()->create();
    $this->actingAs($other_user);

    $response = $this->post(route('comment.store'), [
            'debt_id' => $this->debt->id,
            'content' => 'This is a comment',
            'user_id' => $other_user->id,
        ]);

    $response->assertSessionHasErrors([
        'debt_id' => 'You do not have permission to comment on this debt.',
    ]);
});