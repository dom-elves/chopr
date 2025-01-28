<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\Share;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createUsers();
        $this->createGroupsWithGroupUsers();
        $this->createDebtsWithShares();
        // $this->createShares();
    }

    public function createUsers()
    {
        User::factory()->create([
            'name' => 'Dom Elves',
            'email' => 'dom_elves@hotmail.co.uk',
            'password' => 'password',
            'total_balance' => 00.00,
        ]);

        User::factory(100)->create(); 
        $this->command->info("created 100 users");
    }

    public function createGroupsWithGroupUsers()
    {
        Group::factory(10)->withGroupUsers()->create(); 
        $this->command->info("created 10 groups");

        // if i'm not in a group, add myself to a few of them
        if (GroupUser::where('user_id', 1)->doesntExist()) {
            $random_group_ids = Arr::random(Group::pluck('id')->toArray(), rand(2,10));

            foreach ($random_group_ids as $random_group_id) {
                GroupUser::factory()->create([
                    'group_id' => $random_group_id,
                    'user_id' => 1,
                ]);

                $this->command->info("added self to group ${random_group_id}");
            }
        }
    }

    public function createDebtsWithShares()
    {
        $group_ids = Group::pluck('id')->toArray();
        

        foreach ($group_ids as $group_id) {
            $collector = GroupUser::where('group_id', $group_id)->first();

            Debt::factory(rand(1,3))->withShares()->create([
                'group_id' => $group_id,
                // todo: figure out a way using states to callback to this
                // so the debt owner can be randomised
                'collector_group_user_id' => $collector->user_id,
            ]);
    
            $this->command->info("Debt added for group {$group_id} by {$collector->user->name}");
        } 
    }
}
