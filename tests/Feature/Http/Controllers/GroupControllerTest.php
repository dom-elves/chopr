<?php

use App\Models\User;

beforeEach(function () {
    // Reset the database
    $this->artisan('migrate:fresh --seed');

    // seeder is built so i'm first user & at least in multiple groups with debts etc
    $this->user = User::first();

    $this->actingAs($this->user);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('user groups appear', function() {
    $response = $this->get('/dashboard');

    $response->assertStatus(200);

    foreach ($this->user->groups as $group) {
        $response->assertSee($group->name);
    }
});