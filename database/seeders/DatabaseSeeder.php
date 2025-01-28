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
        $this->createDebts();
        $this->createShares();
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

    public function createDebts()
    {
        $faker = Faker::create();

        $group_ids = Group::pluck('id')->toArray();
        
        foreach ($group_ids as $group_id) {
    
            $amount = random_int(1,999) + round(100/random_int(100,1000), 2);
            $collector = GroupUser::where('group_id', $group_id)->first();
            $user = User::findOrFail($collector->user_id);

            $nouns = file(base_path('app/TextFiles/nouns.txt'), FILE_IGNORE_NEW_LINES);;
            $random_noun = $faker->randomElement($nouns);

            Debt::create([
                'group_id' => $group_id,
                'name' => $random_noun,
                'amount' => $amount,
                'collector_group_user_id' => $collector->user_id,
                // todo: update this to not always split even, but find a way to randomly chunk debts
                'split_even' => 1,
                // todo: update this to eventually have some cleared debts
                'cleared' => 0,
                'currency' => 'GBP',
            ]);
            
            $this->command->info("Debt added for group {$group_id} for {$amount} by {$user->name}");
        } 
    }

    public function createShares()
    {
        $debts = Debt::all();

        foreach ($debts as $debt) {
            $group_id = $debt->group_id;
            $group_users = GroupUser::where('group_id', $group_id)->get();
            $group_users_count = $group_users->count();
            // dd($group_users);
            $split = $debt->amount / $group_users_count;
            $rounded_split = ceil($split * 100) / 100;
            $formatted_split = number_format($rounded_split, 2);

            foreach ($group_users as $group_user) {
                $paid = rand(0,1);

                Share::create([
                   'group_user_id' => $group_user->id,
                   'debt_id' => $debt->id,
                    // todo: update these as mentioned above, ranomly chunking debts
                   'amount' => $formatted_split,
                   'paid_amount' => $paid ? $formatted_split : 0,
                   'cleared' => $paid ? 1 : 0, // for now we'll pretend all paid debts are cleared
                ]);
            }

            $this->command->info("{$group_users_count} shares added for {$debt->name} in group {$group_id}");
        };
    }
}
