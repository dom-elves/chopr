<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Administrator;
use Illuminate\Database\Eloquent\Model;

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
        // $this->withGman();

        Administrator::factory()->create([
            'name' => 'Dom Admin',
            'email' => 'dom_admin@hotmail.co.uk',
            'password' => 'password',
        ]);
    }

    public function createUsers()
    {
        // add self
        $self = User::factory()->create([
            'name' => 'Dom Elves',
            'email' => 'dom_elves@hotmail.co.uk',
            'password' => 'password',
        ]);

        // now a bunch of users so a group can be created
        User::factory(100)->create(); 
        $this->command->info("created 100 users \n");

        // a group to be in
        $group = Group::factory()->withGroupUsers()->create([
            'user_id' => $self->id,
        ]);

        // debt and shares for the group
        Debt::factory()->withShares()->create([
            'group_id' => $group->id,
            'user_id' => $self->id
        ]);

    }

    public function createGroupsWithGroupUsers()
    {
        // take random user ids
        $random_user_ids = Arr::random(User::pluck('id')->toArray(), 10);

        // create groups with group users for them
        foreach ($random_user_ids as $random_ids) {
            Group::factory()->withGroupUsers()->create([
                'user_id' => $random_ids,
            ]);
        } 

        $this->command->info("created 10 groups \n");
    }

    public function createDebtsWithShares()
    {
        $groups = Group::all();
        
        foreach ($groups as $group) {

            // random amount of group_users in the group
            $group_users = $group->group_users;
            $random_group_users = $group_users->shuffle()->take(random_int(1, 3));  
            
            // a debt for each user
            foreach ($random_group_users as $group_user) {
                Debt::factory()->withShares()->create([
                    'group_id' => $group->id,
                    'user_id' => $group_user->user->id,
                ]);
               
                $this->command->info("Debt added for group {$group->id} by {$group_user->user->name}");
            }

            $this->command->info("{$random_group_users->count()} debts added for group {$group->id}\n");
        } 
    }

    public function withGman()
    {
        $gman = User::factory()->create([
            'name' => 'gman',
            'email' => 'gman@gman.com',
            'password' => 'gman',
        ]);

        $group = Group::factory()->withGroupUsers()->create([
            'user_id' => $gman->id,
        ]);

        $self = User::findOrFail(1);
       
        $my_group = Group::factory()->withGroupUsers()->create([
            'user_id' => $self->id,
        ]);

        GroupUser::factory()->create([
            'user_id' => $gman->id,
            'group_id' => $my_group->id,
        ]);
    }
}
