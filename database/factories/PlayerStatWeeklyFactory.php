<?php

namespace Database\Factories;

use App\Enums\Datum;
use App\Enums\SeasonType;
use App\Models\Player;
use App\Models\PlayerStatWeekly;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerStatWeekly>
 */
class PlayerStatWeeklyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlayerStatWeekly::class;

    /**
     * Define the model's default state.
     *
     * A plain line is a pass catcher's, since that is what most rows in the
     * table are. The states below are for the positions whose stats live in a
     * different family altogether.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $targets = fake()->numberBetween(0, 14);
        $receptions = fake()->numberBetween(0, $targets);

        return [
            'player_id'            => Player::factory(),
            'season'               => fake()->numberBetween(2021, 2025),
            'week'                 => fake()->numberBetween(1, 18),
            'season_type'          => SeasonType::REGULAR,
            'source'               => Datum::SOURCE_NFLVERSE->value,
            'receiving_targets'    => $targets,
            'receiving_receptions' => $receptions,
            'receiving_yards'      => $receptions * fake()->numberBetween(0, 20),
            'receiving_touchdowns' => fake()->numberBetween(0, 2),
        ];
    }

    /**
     * A passer's line, where the yards and the attempts have to move together
     * for the row to make any sense.
     */
    public function quarterback(): static
    {
        return $this->state(function () {
            $attempts = fake()->numberBetween(15, 45);
            $completions = fake()->numberBetween(10, $attempts);

            return [
                'passing_attempts'      => $attempts,
                'passing_completions'   => $completions,
                'passing_yards'         => $completions * fake()->numberBetween(5, 15),
                'passing_touchdowns'    => fake()->numberBetween(0, 4),
                'passing_interceptions' => fake()->numberBetween(0, 2),
                'receiving_targets'     => 0,
                'receiving_receptions'  => 0,
                'receiving_yards'       => 0,
                'receiving_touchdowns'  => 0,
            ];
        });
    }

    /**
     * A kicker's line.
     */
    public function kicker(): static
    {
        return $this->state(function () {
            $attempted = fake()->numberBetween(0, 5);
            $made = fake()->numberBetween(0, $attempted);

            return [
                'field_goals_attempted'  => $attempted,
                'field_goals_made'       => $made,
                'extra_points_attempted' => fake()->numberBetween(0, 6),
                'receiving_targets'      => 0,
                'receiving_receptions'   => 0,
                'receiving_yards'        => 0,
                'receiving_touchdowns'   => 0,
            ];
        });
    }

    /**
     * A postseason line.
     */
    public function postseason(): static
    {
        return $this->state([
            'season_type' => SeasonType::POST,
            'week'        => fake()->numberBetween(19, 22),
        ]);
    }
}
