<?php

namespace Database\Factories;

use App\Enums\NFLPositions;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Position::class;

    /**
     * Keep the string primary key in sync with the abbreviation, including
     * for states that override it.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Position $position) {
            $position->id = $position->abbreviation;
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $position = fake()->randomElement(NFLPositions::cases());

        return [
            'abbreviation' => $position->value,
            'name'         => $this->getPositionName($position->value),
        ];
    }

    /**
     * Get the full name for a position abbreviation.
     */
    private function getPositionName(string $abbreviation): string
    {
        return match ($abbreviation) {
            'QB'    => 'Quarterback',
            'RB'    => 'Running Back',
            'WR'    => 'Wide Receiver',
            'TE'    => 'Tight End',
            'K'     => 'Kicker',
            'DST'   => 'Defense/Special Teams',
            default => $abbreviation,
        };
    }

    /**
     * Create a specific position by abbreviation.
     */
    public function quarterback(): static
    {
        return $this->state(fn (array $attributes) => [
            'abbreviation' => 'QB',
            'name'         => 'Quarterback',
        ]);
    }

    public function runningBack(): static
    {
        return $this->state(fn (array $attributes) => [
            'abbreviation' => 'RB',
            'name'         => 'Running Back',
        ]);
    }

    public function wideReceiver(): static
    {
        return $this->state(fn (array $attributes) => [
            'abbreviation' => 'WR',
            'name'         => 'Wide Receiver',
        ]);
    }

    public function tightEnd(): static
    {
        return $this->state(fn (array $attributes) => [
            'abbreviation' => 'TE',
            'name'         => 'Tight End',
        ]);
    }

    public function kicker(): static
    {
        return $this->state(fn (array $attributes) => [
            'abbreviation' => 'K',
            'name'         => 'Kicker',
        ]);
    }

    public function defense(): static
    {
        return $this->state(fn (array $attributes) => [
            'abbreviation' => 'DST',
            'name'         => 'Defense/Special Teams',
        ]);
    }
}
