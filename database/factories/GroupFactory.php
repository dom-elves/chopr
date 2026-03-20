<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Database\Eloquent\Factories\Sequence;

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
            'user_id' => User::all()->random()->id,
        ];
    }

    private function getWords($path) {
        return file(base_path($path), FILE_IGNORE_NEW_LINES);
    }

    /**
     * Takes a param of an int, but without one sets it to randon between 2 and 10.
     * $count determines number of group users created,
     * so pick a random $count of a users and make a group user for each.
     * $sequence->index takes the direct array values, so no duplicates.
     * This is the equivalent to adding a user to a group.
     */
    public function withGroupUsers(?int $count = 0): static
    {
        return $this->afterCreating(function (Group $group) use ($count) {
            $count = $count === 0 ? $count = rand(2, 10) : $count;

            GroupUser::factory()
                ->count($count)
                ->state(new Sequence(
                    fn (Sequence $sequence) => ['user_id' => User::all()
                        ->random($count)
                        ->pluck('id')[$sequence->index]]
                ))
                ->create([
                    'group_id' => $group->id,
                ]);
        });
    }
}
