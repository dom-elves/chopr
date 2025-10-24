<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = ['dom', 'alex', 'gman', 'remi'];

        foreach ($users as $user) {
            User::factory()->create([
                'name' => $user,
                'email' => $user . '@example.com',
                'password' => $user . '123',
            ]);
        }

        $group = Group::factory()->create([
            'name' => 'Test Group',
            'user_id' => User::where('name', 'dom')->first()->id,
        ]);

        $users = User::all();

        foreach ($users as $user) {
            GroupUser::factory()->create([
                'group_id' => $group->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
