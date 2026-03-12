<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\User;
use App\Models\Share;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Brick\Money\Money;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     * 
     * Amount is stored as minor units and is accessed as Money object
     * in the Cash cast.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $nouns = file(base_path('app/TextFiles/nouns.txt'), FILE_IGNORE_NEW_LINES);;
        $faker = Faker::create();
        $random_noun = $faker->randomElement($nouns);

        return [
            'name' => $random_noun,
            'amount' => rand(100, 1000) * 100,
            'split_even' => rand(0,1),
            'cleared' => 0,
            'currency' => 'GBP',
        ];
    }

    public function withShares() {
        return $this->afterCreating(function(Debt $debt) {
            $group_users = $debt->group->group_users;

            if ($debt->split_even) {
                $this->splitEvenShares($debt, $group_users);
            } else {
                $this->chunkSharesRandomly($debt, $group_users);
            }
        });
    }

    public function withComments() {
        return $this->afterCreating(function(Debt $debt) {
            foreach ($debt->group_users as $group_user) {
                Comment::factory()->create([
                    'debt_id' => $debt->id,
                    'group_user_id' => $group_user->id,
                    'content' => "Comment by {$group_user->user->name}"
                ]);
            }
        });
    }

    /**
     * split() is a brick/money method that evenly splits a value into money objects
     */
    private function splitEvenShares($debt, $group_users) {
        $money = $debt->amount->split($group_users->count()); 
  
        foreach ($group_users as $key => $group_user) {
            Share::factory()->calcTotal()->create([
                'group_user_id' => $group_user->id,
                'debt_id' => $debt->id,
                'amount' => $money[$key]->getMinorAmount(),
                'sent' => $group_user->user->id === $debt->user_id ? 1 : rand(0, 1),
                'seen' => 0,
            ]);
        }
    }

    private function chunkSharesRandomly($debt, $group_users) {
        $total = $debt->amount->getMinorAmount()->toInt();
        $count = $group_users->count();
        $chunk = intdiv($total, $count);
        
        foreach ($group_users as $group_user) {
            // give some variance to chunks 
            $split = rand($chunk - 1000, $chunk + 1000);
            // give the last user the remainder of the debt
            $share_amount = $count === 1 ? $total : $split;

            Share::factory()->calcTotal()->create([
                'group_user_id' => $group_user->id,
                'debt_id' => $debt->id,
                'amount' => $share_amount,
                'sent' => $group_user->user_id === $debt->user_id ? 1 : rand(0, 1),
                'seen' => 0,
            ]);
           
            // take away the split each time
            $total -= $split;
            $count--;
        }
    }
}
