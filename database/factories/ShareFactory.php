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
        ];
    }

    /**
     * Calc totals after shares are created
     * The idea is that if your balance is positive, you are owed money & vice versa
     */
    public function calcTotal() {
        return $this->afterCreating(function(Share $share) {
            $debt_owner = $share->debt->user;
            $share_owner = $share->user;
            $share_group_user = $share_owner->group_users->where('user_id', $share->user_id)->first();
            $debt_group_user = $debt_owner->group_users->where('user_id', $debt_owner->id)->first();

            if ($share_group_user->id != $debt_group_user->id) {
                $share_group_user->balance -= $share->amount;
            } else {
                $debt_group_user->balance += $share->amount;
            }

            $debt_group_user->save();
            $share_group_user->save();
        });
    }
}
