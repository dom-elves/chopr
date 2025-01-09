<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;

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
        $this->createGroups();
        $this->createGroupUsers();
        $this->createDebts();
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
    }

    public function createGroups()
    {
        Group::factory(10)->create();     
    }

    public function createGroupUsers()
    {
		$group_ids = Group::pluck('id')->toArray();

        foreach ($group_ids as $group_id) {
            // get some random user ids, excluding my own
            $random_users = User::whereNotIn('id', [1])->pluck('id')->shuffle()->take(random_int(2,10));

            foreach ($random_users as $random_user) {
                GroupUser::create([
                  'group_id' => $group_id,
                  'user_id' => $random_user
                ]);
            }  
        }

        // add myself to a random maount of groups
        $random_group_ids = Arr::random($group_ids, random_int(2,10));

        foreach ($random_group_ids as $random_group_id) {
            $this->addSelfToGroup($random_group_id);
        }
    }

    private function addSelfToGroup($random_group_id)
    {
        GroupUser::create([
            'group_id' => $random_group_id,
            'user_id' => 1,
        ]);
        
        dump('appending self to group '. $random_group_id);
    }
    
    public function createDebts()
    {
        $faker = Faker::create();

        $group_ids = Group::pluck('id')->toArray();
        
        foreach ($group_ids as $group_id) {
    
            $amount = random_int(1,999) + round(100/random_int(100,1000), 2);
            $collector = GroupUser::where('group_id', $group_id)->first();
            $user = User::findOrFail($collector->user_id);

            $nouns = file('nouns.txt', FILE_IGNORE_NEW_LINES);
            $randomNoun = $faker->randomElement($nouns);

            Debt::create([
                'group_id' => $group_id,
                'name' => $randomNoun,
                'amount' => $amount,
                'collector_group_user_id' => $collector->user_id,
                // todo: update this to not always split even, but find a way to randomly chunk debts
                'split_even' => 1,
                // todo: update this to eventually have some cleared debts
                'cleared' => 0,
            ]);
            
            dump("Debt added for group ${group_id} for ${amount} by " . $user->name);
        } 
    }
}
