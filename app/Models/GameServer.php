<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameServer extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'game_id',
        'description',
        'max_players',
        'is_active',
        'is_online',
        'port_open',
        'last_checked_at',
    ];

    /**
     * Get the game that this server is for.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
