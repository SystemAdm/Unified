<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::query()->where('is_active', true);

        // Filter by console if provided
        if ($request->has('console')) {
            $query->where('console', $request->console);
        }

        // Filter by game name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $games = $query->get();

        // Get unique console types for filter dropdown
        $consoles = Game::where('is_active', true)
            ->select('console')
            ->distinct()
            ->pluck('console');

        return Inertia::render('games/Index', [
            'games' => $games,
            'consoles' => $consoles,
            'filters' => $request->only(['console', 'search'])
        ]);
    }

    public function show(Game $game)
    {
        // Make sure the game is active
        if (!$game->is_active) {
            abort(404);
        }

        return Inertia::render('games/Show', [
            'game' => $game
        ]);
    }
}
