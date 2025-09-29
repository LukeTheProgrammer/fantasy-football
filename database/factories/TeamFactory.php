<?php

namespace Database\Factories;

use App\Enums\NFLConferences;
use App\Enums\NFLDivisions;
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
        $conference = fake()->randomElement(NFLConferences::cases());
        $division = $this->getRandomDivisionForConference($conference);

        return [
            'espn_id'      => fake()->optional(0.8)->numberBetween(1, 1000),
            'abbreviation' => fake()->unique()->regexify('[A-Z]{2,3}'),
            'location'     => fake()->city(),
            'name'         => fake()->randomElement([
                'Bears', 'Bengals', 'Bills', 'Broncos', 'Browns', 'Buccaneers',
                'Cardinals', 'Chargers', 'Chiefs', 'Colts', 'Cowboys', 'Dolphins',
                'Eagles', 'Falcons', 'Giants', 'Jaguars', 'Jets', 'Lions',
                'Packers', 'Panthers', 'Patriots', 'Raiders', 'Rams', 'Ravens',
                'Saints', 'Seahawks', 'Steelers', 'Texans', 'Titans', 'Vikings',
                'Commanders', '49ers',
            ]),
            'logo'       => fake()->optional(0.7)->imageUrl(200, 200, 'sports'),
            'conference' => $conference->value,
            'division'   => $division->value,
        ];
    }

    /**
     * Get a random division for the given conference.
     */
    private function getRandomDivisionForConference(NFLConferences $conference): NFLDivisions
    {
        $divisions = match ($conference) {
            NFLConferences::AFC => [
                NFLDivisions::AFC_EAST,
                NFLDivisions::AFC_NORTH,
                NFLDivisions::AFC_SOUTH,
                NFLDivisions::AFC_WEST,
            ],
            NFLConferences::NFC => [
                NFLDivisions::NFC_EAST,
                NFLDivisions::NFC_NORTH,
                NFLDivisions::NFC_SOUTH,
                NFLDivisions::NFC_WEST,
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
            'conference' => NFLConferences::AFC->value,
            'division'   => fake()->randomElement([
                NFLDivisions::AFC_EAST->value,
                NFLDivisions::AFC_NORTH->value,
                NFLDivisions::AFC_SOUTH->value,
                NFLDivisions::AFC_WEST->value,
            ]),
        ]);
    }

    public function nfc(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::NFC->value,
            'division'   => fake()->randomElement([
                NFLDivisions::NFC_EAST->value,
                NFLDivisions::NFC_NORTH->value,
                NFLDivisions::NFC_SOUTH->value,
                NFLDivisions::NFC_WEST->value,
            ]),
        ]);
    }

    /**
     * Create a team in a specific division.
     */
    public function afcEast(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::AFC->value,
            'division'   => NFLDivisions::AFC_EAST->value,
        ]);
    }

    public function afcNorth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::AFC->value,
            'division'   => NFLDivisions::AFC_NORTH->value,
        ]);
    }

    public function afcSouth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::AFC->value,
            'division'   => NFLDivisions::AFC_SOUTH->value,
        ]);
    }

    public function afcWest(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::AFC->value,
            'division'   => NFLDivisions::AFC_WEST->value,
        ]);
    }

    public function nfcEast(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::NFC->value,
            'division'   => NFLDivisions::NFC_EAST->value,
        ]);
    }

    public function nfcNorth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::NFC->value,
            'division'   => NFLDivisions::NFC_NORTH->value,
        ]);
    }

    public function nfcSouth(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::NFC->value,
            'division'   => NFLDivisions::NFC_SOUTH->value,
        ]);
    }

    public function nfcWest(): static
    {
        return $this->state(fn (array $attributes) => [
            'conference' => NFLConferences::NFC->value,
            'division'   => NFLDivisions::NFC_WEST->value,
        ]);
    }
}
