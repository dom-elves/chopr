<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Support\Arr;

use Faker\Factory as Faker;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create();

        // random verbs & nouns to make group name
        $verbs = $this->getWords('app/TextFiles/verbs.txt');
        $nouns = $this->getWords('app/TextFiles/nouns.txt');
        $random_verb = $faker->randomElement($verbs);
        $random_noun = $faker->randomElement($nouns);

        return [
            'name' => "The {$random_verb} {$random_noun}",
        ];
    }

    public function withGroupUsers() {
        return $this->afterCreating(function(Group $group) {
            $user_ids = User::whereNot('id', $group->user_id)
                ->pluck('id')
                ->shuffle()
                ->take(random_int(2,10));

            foreach ($user_ids as $user_id) {
                GroupUser::factory()->create([
                    'group_id' => $group->id,
                    'user_id' => $user_id,
                  ]);
            }
        });
    }

    private function getWords($path) {
        return file(base_path($path), FILE_IGNORE_NEW_LINES);
    }
}
