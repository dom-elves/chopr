<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;

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
        // $this->createDebtsWithShares();
        // $this->createComments();
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
     * Create at least one group for self & 50 other groups with random amount of users.
     */
    public function createGroupsWithGroupUsers()
    {
        Group::factory()
            ->hasGroupUsers(5)
            ->create([
                'user_id' => User::first()->id,
            ]);

        Group::factory()
            ->count(50)
            ->hasGroupUsers(rand(2,10))
            ->create();
        
        $this->command->info("50 groups each with 5 group users created \n");
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
                    'group_user_id' => $group_user->id,
                ]);
               
                $this->command->info("Debt added for group {$group->id} by {$group_user->user->name}");
            }

            $this->command->info("{$random_group_users->count()} debts added for group {$group->id}\n");
        } 
    }

    public function createComments()
    {
        $debts = Debt::all();

        foreach ($debts as $debt) {
            // 50/50 on whether or not we add a comment
            $add_comments = random_int(0,1);
            
            if ($add_comments) {
                // random number of comments
                $num_comments = random_int(1, 5);

                for ($i = 0; $i < $num_comments; $i++) {
                    Comment::factory()->create([
                        'debt_id' => $debt->id,
                        'group_user_id' => Arr::random($debt->group->group_users->pluck('id')->toArray()),
                    ]);
                }

                $this->command->info("{$num_comments} comments added for debt {$debt->id}");
            }
        }
    }

}
