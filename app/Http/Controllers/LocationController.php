<?php

namespace App\Http\Controllers;

use App\Enum\Permission;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::orderBy('name')
            ->paginate(9);

        return Inertia::render('locations/Index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {

        // Load related events
        $location->load(['events' => function ($query) {
            $query->where('status', 'published')
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(5);
        }]);

        return Inertia::render('locations/Show', [
            'location' => $location,
        ]);
    }
}
