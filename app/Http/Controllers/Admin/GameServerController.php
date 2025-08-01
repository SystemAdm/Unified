<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameServer;
use App\Services\ServerStatusService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameServerController extends Controller
{
    /**
     * Display a listing of the game servers.
     */
    public function index()
    {
        $gameServers = GameServer::with('game')->orderBy('name')->paginate(10);
        return Inertia::render('admin/gameservers/Index', compact(['gameServers']));
    }

    /**
     * Show the form for creating a new game server.
     */
    public function create()
    {
        $games = Game::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('admin/gameservers/Create', compact(['games']));
    }

    /**
     * Store a newly created game server in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'game_id' => 'required|exists:games,id',
            'description' => 'nullable|string',
            'max_players' => 'nullable|integer|min:0',
        ]);

        // Create the game server
        GameServer::create([
            'name' => $validated['name'],
            'ip_address' => $validated['ip_address'],
            'port' => $validated['port'],
            'game_id' => $validated['game_id'],
            'description' => $validated['description'] ?? null,
            'max_players' => $validated['max_players'] ?? 0,
            'is_active' => true, // Default to active
        ]);

        return redirect()->route('admin.gameservers.index')
            ->with('success', 'Game server created successfully.');
    }

    /**
     * Display the specified game server.
     */
    public function show(GameServer $gameserver)
    {
        $gameserver->load('game');
        return Inertia::render('admin/gameservers/Show', [
            'gameServer' => $gameserver
        ]);
    }

    /**
     * Show the form for editing the specified game server.
     */
    public function edit(GameServer $gameserver)
    {
        $games = Game::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('admin/gameservers/Edit', [
            'gameServer' => $gameserver,
            'games' => $games
        ]);
    }

    /**
     * Update the specified game server in storage.
     */
    public function update(Request $request, GameServer $gameserver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'game_id' => 'required|exists:games,id',
            'description' => 'nullable|string',
            'max_players' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Update the game server
        $gameserver->update([
            'name' => $validated['name'],
            'ip_address' => $validated['ip_address'],
            'port' => $validated['port'],
            'game_id' => $validated['game_id'],
            'description' => $validated['description'] ?? $gameserver->description,
            'max_players' => $validated['max_players'] ?? $gameserver->max_players,
            'is_active' => $validated['is_active'] ?? $gameserver->is_active,
        ]);

        return redirect()->route('admin.gameservers.index')
            ->with('success', 'Game server updated successfully.');
    }

    /**
     * Remove the specified game server from storage.
     */
    public function destroy(GameServer $gameserver)
    {
        $gameserver->delete();

        return redirect()->route('admin.gameservers.index')
            ->with('success', 'Game server deleted successfully.');
    }

    /**
     * Check the status of a game server (ping and port)
     */
    public function checkStatus(GameServer $gameserver, ServerStatusService $serverStatusService)
    {
        // Get server status
        $status = $serverStatusService->getServerStatus(
            $gameserver->ip_address,
            $gameserver->port
        );

        // Update the game server with status information
        $gameserver->update([
            'is_online' => $status['is_online'],
            'port_open' => $status['port_open'],
            'last_checked_at' => $status['checked_at'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Server status checked successfully',
            'data' => [
                'id' => $gameserver->id,
                'is_online' => $gameserver->is_online,
                'port_open' => $gameserver->port_open,
                'last_checked_at' => $gameserver->last_checked_at,
            ]
        ]);
    }
}
