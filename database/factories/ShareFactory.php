<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use App\Models\Share;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Share>
 */
class ShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // done in the same way as DebtFactory, but only gives some shares a name
        $nouns = file(base_path('app/TextFiles/nouns.txt'), FILE_IGNORE_NEW_LINES);;
        $faker = Faker::create();
        $random_noun = $faker->randomElement($nouns);
        
        return [
            'name' => rand(0,1) ? $random_noun : '',
            'sent' => 0,
            'seen' => 0,
        ];
    }

    /**
     * Some randomisation on whether or not a share is seeded as sent & seen.
     */
    public function configure(): static
    {
        return $this->afterCreating(function(Share $share) {
            $share->sent = $share->debt->group_user_id === $share->group_user_id ? 1 : rand(0, 1);
            $share->seen = $share->sent ? rand(0, 1) : 0;
        });
    }

    /**
     * Calc totals after shares are created
     * The idea is that if your balance is positive, you are owed money & vice versa
     */
    public function calcTotal() {
        return $this->afterCreating(function(Share $share) {

            $share_group_user = $share->groupUser;
            $debt_group_user = $share->debt->groupUser;
            
            if ($share_group_user->id != $debt_group_user->id) {
                // same as in BalanceService, calc user balance depending on debt
                $share_group_user->balance = $share_group_user->balance->minus($share->amount);
                $debt_group_user->balance = $debt_group_user->balance->plus($share->amount);

                $debt_group_user->save();
                $share_group_user->save();
            }   

            // as 'seen' is just cosmetic, randomise whether or not
            // as 'sent' share is also seen
            if ($share->sent) {
                $share->seen =  $share->group_user_id === $share->debt->group_user_id ? 1 : rand(0,1);
                $share->save();
            }
        });
    }
}
