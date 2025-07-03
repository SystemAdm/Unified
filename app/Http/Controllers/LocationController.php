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
        $this->authorize(Permission::INDEX_LOCATION->value);
        $locations = Location::orderBy('name')
            ->paginate(12);

        return Inertia::render('locations/Index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        $this->authorize(Permission::SHOW_LOCATION->value);

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
