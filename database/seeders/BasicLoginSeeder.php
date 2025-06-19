<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;

class BasicLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add self & some users, just as a 'clean' version of the app to play with
        $self = User::factory()->create([
            'name' => 'Dom Elves',
            'email' => 'dom_elves@hotmail.co.uk',
            'password' => 'password',
        ]);

        User::factory(100)->create(); 
        $this->command->info("created 100 users \n");

        $group = Group::factory()->withGroupUsers()->create([
            'user_id' => $self->id,
        ]);
    }
}
