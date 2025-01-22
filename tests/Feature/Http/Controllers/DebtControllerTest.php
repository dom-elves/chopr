<?php

use App\Models\User;
Use App\Models\Group;

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

// todo: move this when eventually moving dash logic to controller
test('dashboard can be rendered', function() {
    $response = $this->get('/dashboard');

    $response->assertStatus(200);
});


