<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use App\Models\Share;
use App\Services\LedgerService;

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

            $ledger = new LedgerService();
            $ledger->createLedgerEntry($share);
        });
    }
}
