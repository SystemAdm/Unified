<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Equipment;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::orderBy('name')->paginate(10);
        return Inertia::render('admin/games/Index', compact(['games']));
    }

    public function create()
    {
        $types = Equipment::cases();
        return Inertia::render('admin/games/Create', compact(['types']));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|integer',
            'version' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
        ]);

        // Get the equipment type from the enum
        $equipment = Equipment::cases()[$validated['type']];

        // Prepare game data
        $gameData = [
            'name' => $validated['name'],
            'version' => $validated['version'],
            'console' => $equipment->value,
            'is_active' => true, // Default to active
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('games', 'public');
            $gameData['image'] = $path;
        }

        // Create the game
        $game = Game::create($gameData);

        return redirect()->route('admin.games.index')
            ->with('success', 'Game created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        return Inertia::render('admin/games/Show', [
            'game' => $game
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Game $game)
    {
        $types = Equipment::cases();
        // Find the index of the current console in the Equipment enum
        $currentTypeIndex = array_search($game->console, array_map(fn($type) => $type->value, $types));

        return Inertia::render('admin/games/Edit', [
            'game' => $game,
            'types' => $types,
            'currentTypeIndex' => $currentTypeIndex
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|integer',
            'version' => 'required|string|max:255',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
        ]);

        // Get the equipment type from the enum
        $equipment = Equipment::cases()[$validated['type']];

        // Prepare game data for update
        $gameData = [
            'name' => $validated['name'],
            'version' => $validated['version'],
            'console' => $equipment->value,
            'is_active' => $validated['is_active'] ?? $game->is_active,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($game->image) {
                Storage::disk('public')->delete($game->image);
            }

            $path = $request->file('image')->store('games', 'public');
            $gameData['image'] = $path;
        }

        // Update the game
        $game->update($gameData);

        return redirect()->route('admin.games.index')
            ->with('success', 'Game updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('admin.games.index')
            ->with('success', 'Game deleted successfully.');
    }
}
