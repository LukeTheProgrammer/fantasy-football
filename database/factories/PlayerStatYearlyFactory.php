<?php

namespace Database\Factories;

use App\Enums\Datum;
use App\Enums\SeasonType;
use App\Models\Player;
use App\Models\PlayerStatYearly;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerStatYearly>
 */
class PlayerStatYearlyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlayerStatYearly::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $games = fake()->numberBetween(1, 17);
        $targets = $games * fake()->numberBetween(0, 9);
        $receptions = fake()->numberBetween(0, $targets);

        return [
            'player_id'            => Player::factory(),
            'season'               => fake()->numberBetween(2021, 2025),
            'season_type'          => SeasonType::REGULAR,
            'source'               => Datum::SOURCE_NFLVERSE->value,
            'games_played'         => $games,
            'receiving_targets'    => $targets,
            'receiving_receptions' => $receptions,
            'receiving_yards'      => $receptions * fake()->numberBetween(0, 18),
            'receiving_touchdowns' => fake()->numberBetween(0, 15),
        ];
    }

    /**
     * A passer's season, where the volume columns have to agree with each
     * other.
     */
    public function quarterback(): static
    {
        return $this->state(function (array $attributes) {
            $attempts = ($attributes['games_played'] ?? 17) * fake()->numberBetween(20, 38);
            $completions = fake()->numberBetween((int) ($attempts * 0.5), $attempts);

            return [
                'passing_attempts'      => $attempts,
                'passing_completions'   => $completions,
                'passing_yards'         => $completions * fake()->numberBetween(8, 13),
                'passing_touchdowns'    => fake()->numberBetween(5, 45),
                'passing_interceptions' => fake()->numberBetween(0, 20),
                'receiving_targets'     => 0,
                'receiving_receptions'  => 0,
                'receiving_yards'       => 0,
                'receiving_touchdowns'  => 0,
            ];
        });
    }
}
