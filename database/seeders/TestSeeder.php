<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;

class TestSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = ['dom', 'alex', 'gman', 'remi', 'louis'];
     
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
            GroupUser::factory()->withAliases()->create([
                'group_id' => $group->id,
                'user_id' => $user->id,
            ]);
        }

        Debt::factory()
            ->withShares()
            ->create([
                'group_id' => $group->id,
                'group_user_id' => GroupUser::where('user_id', User::where('name', 'dom')->first()->id)->first()->id,
            ]);
    }
}
