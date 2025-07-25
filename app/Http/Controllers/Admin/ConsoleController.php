<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Console;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $consoles = Console::orderBy('name')->paginate(10);
        return Inertia::render('admin/consoles/Index', compact(['consoles']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/consoles/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpu' => 'nullable|string|max:255',
            'gpu' => 'nullable|string|max:255',
            'psu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'hdd' => 'nullable|string|max:255',
            'ssd' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create the console
        $console = Console::create($validated);

        return redirect()->route('admin.consoles.index')
            ->with('success', 'Console created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Console $console)
    {
        return Inertia::render('admin/consoles/Show', [
            'console' => $console
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Console $console)
    {
        return Inertia::render('admin/consoles/Edit', [
            'console' => $console
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Console $console)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpu' => 'nullable|string|max:255',
            'gpu' => 'nullable|string|max:255',
            'psu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'hdd' => 'nullable|string|max:255',
            'ssd' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Update the console
        $console->update($validated);

        return redirect()->route('admin.consoles.index')
            ->with('success', 'Console updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Console $console)
    {
        $console->delete();

        return redirect()->route('admin.consoles.index')
            ->with('success', 'Console deleted successfully.');
    }
}
