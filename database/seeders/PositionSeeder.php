<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/positions.json');
        $json = file_get_contents($path);
        $positions = json_decode($json, true);

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['id' => Arr::get($position, 'abbreviation')],
                [
                    'abbreviation' => Arr::get($position, 'abbreviation'),
                    'name'         => Arr::get($position, 'name'),
                ]
            );
        }
    }
}
