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
    }

    public function createUsers()
    {
        // add self
        $self = User::factory()->create([
            'name' => 'Dom Elves',
            'email' => 'dom_elves@hotmail.co.uk',
            'password' => 'password',
            'total_balance' => 00.00,
        ]);

        // now a bunch of users so a group can be created
        User::factory(100)->create(); 
        $this->command->info("created 100 users");

        // a group to be in
        $group = Group::factory()->withGroupUsers()->create([
            'owner_id' => $self->id,
        ]);

        // and the group user that's been created for myself
        $self_group_user = GroupUser::where('user_id', $self->id)
            ->where('group_id', $group->id)
            ->get();
    
        // debt and shares for the group
        Debt::factory()->withShares()->create([
            'group_id' => $group->id,
            'collector_group_user_id' => $self_group_user[0]->id,
        ]);
    }

    public function createGroupsWithGroupUsers()
    {
        // take random user ids
        $random_user_ids = Arr::random(User::pluck('id')->toArray(), 10);

        // create groups with group users for them
        foreach ($random_user_ids as $random_ids) {
            Group::factory()->withGroupUsers()->create([
                'owner_id' => $random_ids,
            ]);
        } 

        $this->command->info("created 10 groups");
    }

    public function createDebtsWithShares()
    {
        // random group ids
        $group_ids = Group::pluck('id')->toArray();
        
        // crate debt & shares, wit the first group user being the 'collector'
        foreach ($group_ids as $group_id) {
            $collector = GroupUser::where('group_id', $group_id)->first();

            Debt::factory(rand(1,3))->withShares()->create([
                'group_id' => $group_id,
                // todo: figure out a way using states to callback to this
                // so the debt owner can be randomised
                'collector_group_user_id' => $collector->id,
            ]);
    
            $this->command->info("Debt added for group {$group_id} by {$collector->user->name}");
        } 
    }
}
