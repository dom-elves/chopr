<?php

use App\Models\User;
Use App\Models\Group;
use App\Models\Debt;
use App\Models\GroupUser;
use App\Models\Comment;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

beforeEach(function () {
    // Reset the database
    $this->artisan('migrate:fresh --seed');

    // seeder is built so i'm first user & at least in multiple groups with debts etc
    $this->user = User::first();
    $this->group_user = GroupUser::where('user_id', $this->user->id)->first();
    $this->group = $this->group_user->group;
    $this->debt = Debt::where('group_id', $this->group->id)->first();

    $this->actingAs($this->user);
});

test('user can comment on a debt', function () {
    $response = $this->post(route('comment.store'), [
        'debt_id' => $this->debt->id,
        'content' => 'This is a comment',
        'user_id' => $this->user->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('comments', [
        'debt_id' => $this->debt->id,
        'content' => 'This is a comment',
        'user_id' => $this->user->id,
    ]);
});

test('user can edit their comment on a debt', function () {
    // todo: create a comment factory & update seeder
    $comment = Comment::create([
        'user_id' => $this->user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    $response = $this->patch(route('comment.update'), [
        'id' => $comment->id,
        'debt_id' => $comment->debt_id,
        'content' => 'I have now been updated',
        'user_id' => $comment->user_id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('comments', [
        'content' => 'I have now been updated',
    ]);
});

test('user can delete their comment on a debt', function () {
    $comment = Comment::create([
        'user_id' => $this->user->id,
        'debt_id' => $this->debt->id,
        'content' => 'I am a comment on a debt',
    ]);

    $response = $this->delete(route('comment.destroy'), [
        'id' => $comment->id,
        'user_id' => $comment->user_id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('comments', [
        'deleted_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ]);
});

test('user can not edit another user comment on a debt', function () {
    
});

test('user can not delete another user comment on a debt', function () {
    
});