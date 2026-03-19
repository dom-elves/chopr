<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createUsers();
        $this->createGroupsWithGroupUsers();
        $this->createDebtsWithSharesAndComments();
    }

    /**
     * Create self & 100 users
     */
    public function createUsers()
    {
        User::factory()->create([
            'name' => 'Dom Elves',
            'email' => 'dom_elves@hotmail.co.uk',
            'password' => 'password',
        ]);

        User::factory()
            ->count(100)
            ->create();

        $this->command->info("self & 100 users created \n");
    }

    /**
     * Create at least one group with myself & some users,
     * and create 50 groups with 2-10 users.
     */
    public function createGroupsWithGroupUsers()
    {
        $group = Group::factory()
            ->create([
                'user_id' => User::first()->id,
            ]);
        
        GroupUser::factory()
            ->create([
                'user_id' => User::first()->id,
                'group_id' => $group->id,
            ]);

        GroupUser::factory()
            ->count(rand(2,10))
            ->create([
                'group_id' => $group->id,
            ]);

        Group::factory()
            ->count(50)
            ->hasGroupUsers(rand(2,10))
            ->create();

        $this->command->info("50 groups each with 5 group users created \n");
    }

    /**
     * Create 1000 debts, with a random amount of comments, at least one for me.
     */
    public function createDebtsWithSharesAndComments()
    {
        $self = User::first();

        Debt::factory()
            ->withShares()
            ->hasComments(rand(0,5))
            ->create([
                'group_user_id' => GroupUser::where('user_id', $self->id)->first()->id,
            ]);

        Debt::factory()
            ->count(1000)
            ->withShares()
            ->hasComments(rand(0,5))
            ->create();

        $this->command->info("1000 debts created across groups \n");
    }
}
