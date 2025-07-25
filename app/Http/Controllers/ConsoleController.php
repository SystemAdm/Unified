<?php

namespace App\Http\Controllers;

use App\Models\Console;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsoleController extends Controller
{
    /**
     * Display a listing of the consoles.
     */
    public function index(Request $request)
    {
        $query = Console::where('is_active', true);

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cpu', 'like', "%{$search}%")
                  ->orWhere('gpu', 'like', "%{$search}%")
                  ->orWhere('ram', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $consoles = $query->orderBy('name')->paginate(12)->withQueryString();

        return Inertia::render('consoles/Index', [
            'consoles' => $consoles,
            'filters' => $request->only('search')
        ]);
    }
}
