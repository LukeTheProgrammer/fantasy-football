<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'espn_id'     => fake()->optional(0.8)->numberBetween(1000000, 9999999),
            'position_id' => Position::factory(),
            'first_name'  => fake()->firstNameMale(),
            'last_name'   => fake()->lastName(),
            'height'      => fake()->optional(0.9)->randomElement(['5\'10"', '5\'11"', '6\'0"', '6\'1"', '6\'2"', '6\'3"', '6\'4"', '6\'5"', '6\'6"', '6\'7"']),
            'weight'      => fake()->optional(0.9)->numberBetween(180, 350) . ' lbs',
            'college'     => fake()->optional(0.8)->randomElement([
                'Alabama', 'Ohio State', 'Michigan', 'Georgia', 'Clemson', 'Notre Dame',
                'Oklahoma', 'Texas', 'USC', 'LSU', 'Florida', 'Penn State',
                'Oregon', 'Auburn', 'Wisconsin', 'Stanford', 'Michigan State', 'Iowa',
                'Texas A&M', 'Virginia Tech', 'Miami (FL)', 'Florida State', 'Tennessee',
                'Kentucky', 'Mississippi State', 'Ole Miss', 'Arkansas', 'Missouri',
            ]),
            'draft_year'  => fake()->optional(0.7)->numberBetween(2015, 2024),
            'draft_round' => fake()->optional(0.6)->randomElement(['1', '2', '3', '4', '5', '6', '7', 'UDFA']),
            'draft_pick'  => fake()->optional(0.5)->numberBetween(1, 32),
            'draft_team'  => fake()->optional(0.5)->randomElement([
                'Kansas City Chiefs', 'Philadelphia Eagles', 'Cincinnati Bengals', 'Buffalo Bills',
                'San Francisco 49ers', 'Dallas Cowboys', 'Green Bay Packers', 'New England Patriots',
            ]),
            'birth_date' => fake()->optional(0.8)->dateTimeBetween('-40 years', '-20 years'),
            'headshot'   => fake()->optional(0.6)->imageUrl(300, 300, 'people'),
        ];
    }

    /**
     * Create a player with a specific position.
     */
    public function quarterback(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => Position::factory()->quarterback(),
        ]);
    }

    public function runningBack(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => Position::factory()->runningBack(),
        ]);
    }

    public function wideReceiver(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => Position::factory()->wideReceiver(),
        ]);
    }

    public function tightEnd(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => Position::factory()->tightEnd(),
        ]);
    }

    public function kicker(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => Position::factory()->kicker(),
        ]);
    }

    public function defense(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => Position::factory()->defense(),
        ]);
    }

    /**
     * Create a player with specific draft information.
     */
    public function firstRoundPick(): static
    {
        return $this->state(fn (array $attributes) => [
            'draft_round' => '1',
            'draft_pick'  => fake()->numberBetween(1, 32),
        ]);
    }

    public function undrafted(): static
    {
        return $this->state(fn (array $attributes) => [
            'draft_round' => 'UDFA',
            'draft_pick'  => null,
        ]);
    }

    /**
     * Create a player with specific age range.
     */
    public function rookie(): static
    {
        return $this->state(fn (array $attributes) => [
            'draft_year' => 2024,
            'birth_date' => fake()->dateTimeBetween('-25 years', '-20 years'),
        ]);
    }

    public function veteran(): static
    {
        return $this->state(fn (array $attributes) => [
            'draft_year' => fake()->numberBetween(2010, 2019),
            'birth_date' => fake()->dateTimeBetween('-35 years', '-25 years'),
        ]);
    }

    public function experienced(): static
    {
        return $this->state(fn (array $attributes) => [
            'draft_year' => fake()->numberBetween(2005, 2015),
            'birth_date' => fake()->dateTimeBetween('-40 years', '-30 years'),
        ]);
    }
}
