<?php

namespace Database\Factories;

use App\Enums\ConferenceEnum;
use App\Enums\DivisionEnum;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $conference = fake()->randomElement(ConferenceEnum::cases());
        $division = $this->getRandomDivisionForConference($conference);

        return [
            'espn_id' => fake()->optional(0.8)->numberBetween(1, 1000),
            'abbreviation' => fake()->unique()->regexify('[A-Z]{2,3}'),
            'location' => fake()->city(),
            'name' => fake()->randomElement([
                'Bears', 'Bengals', 'Bills', 'Broncos', 'Browns', 'Buccaneers',
                'Cardinals', 'Chargers', 'Chiefs', 'Colts', 'Cowboys', 'Dolphins',
                'Eagles', 'Falcons', 'Giants', 'Jaguars', 'Jets', 'Lions',
                'Packers', 'Panthers', 'Patriots', 'Raiders', 'Rams', 'Ravens',
                'Saints', 'Seahawks', 'Steelers', 'Texans', 'Titans', 'Vikings',
                'Commanders', '49ers',
            ]),
            'logo' => fake()->optional(0.7)->imageUrl(200, 200, 'sports'),
            'conference' => $conference->value,
            'division' => $division->value,
        ];
    }

    /**
     * Get a random division for the given conference.
     */
    private function getRandomDivisionForConference(ConferenceEnum $conference): DivisionEnum
    {
        $divisions = match ($conference) {
            ConferenceEnum::AFC => [
                DivisionEnum::AFC_EAST,
                DivisionEnum::AFC_NORTH,
                DivisionEnum::AFC_SOUTH,
                DivisionEnum::AFC_WEST,
            ],
            ConferenceEnum::NFC => [
                DivisionEnum::NFC_EAST,
                DivisionEnum::NFC_NORTH,
                DivisionEnum::NFC_SOUTH,
                DivisionEnum::NFC_WEST,
            ],
        };

        return fake()->randomElement($divisions);
    }

    /**
     * Create a team in a specific conference.
     */
    public function afc(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::AFC->value,
            'division' => fake()->randomElement([
                DivisionEnum::AFC_EAST->value,
                DivisionEnum::AFC_NORTH->value,
                DivisionEnum::AFC_SOUTH->value,
                DivisionEnum::AFC_WEST->value,
            ]),
        ]);
    }

    public function nfc(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::NFC->value,
            'division' => fake()->randomElement([
                DivisionEnum::NFC_EAST->value,
                DivisionEnum::NFC_NORTH->value,
                DivisionEnum::NFC_SOUTH->value,
                DivisionEnum::NFC_WEST->value,
            ]),
        ]);
    }

    /**
     * Create a team in a specific division.
     */
    public function afcEast(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::AFC->value,
            'division' => DivisionEnum::AFC_EAST->value,
        ]);
    }

    public function afcNorth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::AFC->value,
            'division' => DivisionEnum::AFC_NORTH->value,
        ]);
    }

    public function afcSouth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::AFC->value,
            'division' => DivisionEnum::AFC_SOUTH->value,
        ]);
    }

    public function afcWest(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::AFC->value,
            'division' => DivisionEnum::AFC_WEST->value,
        ]);
    }

    public function nfcEast(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::NFC->value,
            'division' => DivisionEnum::NFC_EAST->value,
        ]);
    }

    public function nfcNorth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::NFC->value,
            'division' => DivisionEnum::NFC_NORTH->value,
        ]);
    }

    public function nfcSouth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::NFC->value,
            'division' => DivisionEnum::NFC_SOUTH->value,
        ]);
    }

    public function nfcWest(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => ConferenceEnum::NFC->value,
            'division' => DivisionEnum::NFC_WEST->value,
        ]);
    }
}
