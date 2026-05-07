<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\Comment;

class DatabaseSeeder extends Seeder
{
    /**
     * Using WithoutModelEvents prevents group observers from firing,
     * which prevents notification from firing. For testing/reference purposes,
     * group stuff lives in observers, debt stuff lives in controller/service. 
     */
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
     * and create 50 groups, withGroupUsers() adds 2-10 by default, unless specified.
     */
    public function createGroupsWithGroupUsers()
    {
        $group = Group::factory()
            ->withGroupUsers()
            ->create([
                'user_id' => User::first()->id,
            ]);

        GroupUser::factory()
            ->create([
                'user_id' => User::first()->id,
                'group_id' => $group->id,
            ]);

        Group::factory()
            ->count(50)
            ->withGroupUsers()
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
            ->withComments()
            ->create([
                'group_user_id' => GroupUser::where('user_id', $self->id)->first()->id,
            ]);

        Debt::factory()
            ->count(1000)
            ->withShares()
            ->withComments()
            ->create();

        $this->command->info("1000 debts with 0-5 comments created across groups \n");
    }
}
