<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameServer;
use Illuminate\Database\Seeder;

class GameServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some games first if none exist
        if (Game::count() === 0) {
            Game::factory(5)->create();
        }

        // Get existing game IDs
        $gameIds = Game::pluck('id')->toArray();

        // Create some predefined game servers
        $servers = [
            [
                'name' => 'Minecraft Survival',
                'ip_address' => '192.168.1.100',
                'port' => 25565,
                'game_id' => $gameIds[0] ?? Game::factory()->create()->id,
                'description' => 'Our primary Minecraft survival server with no mods.',
                'max_players' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'CS:GO Competitive',
                'ip_address' => '192.168.1.101',
                'port' => 27015,
                'game_id' => $gameIds[1] ?? Game::factory()->create()->id,
                'description' => 'Counter-Strike: Global Offensive competitive server.',
                'max_players' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Rust Community',
                'ip_address' => '192.168.1.102',
                'port' => 28015,
                'game_id' => $gameIds[2] ?? Game::factory()->create()->id,
                'description' => 'Rust community server with weekly wipes.',
                'max_players' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($servers as $server) {
            GameServer::create($server);
        }

        // Create additional random game servers
        GameServer::factory(7)
            ->create([
                'game_id' => fn() => $gameIds[array_rand($gameIds)] ?? Game::factory()->create()->id,
            ]);
    }
}
