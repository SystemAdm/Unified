<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample games
        $games = [
            [
                'name' => 'Super Mario Bros',
                'version' => '1.0',
                'console' => 'Nintendo Entertainment System',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'The Legend of Zelda: Breath of the Wild',
                'version' => '1.6',
                'console' => 'Nintendo Switch',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Halo: Combat Evolved',
                'version' => '1.0',
                'console' => 'Xbox',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'God of War',
                'version' => '1.0',
                'console' => 'PlayStation 4',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Minecraft',
                'version' => '1.19',
                'console' => 'PC',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Fortnite',
                'version' => '22.40',
                'console' => 'Multiple',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Final Fantasy VII',
                'version' => '1.0',
                'console' => 'PlayStation',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Tetris',
                'version' => '1.0',
                'console' => 'Game Boy',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Pac-Man',
                'version' => '1.0',
                'console' => 'Arcade',
                'image' => null,
                'is_active' => false,
            ],
            [
                'name' => 'Street Fighter II',
                'version' => 'Turbo',
                'console' => 'Super Nintendo',
                'image' => null,
                'is_active' => false,
            ],
        ];

        foreach ($games as $gameData) {
            Game::create($gameData);
        }
    }
}
