<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Location;
use App\Models\LocationImage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends AdminController
{
    /**
     * Constructor to authorize admin access
     */
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_LOCATION->value);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize(Permission::INDEX_LOCATION->value);

        $locations = Location::with('images')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('admin/locations/Index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize(Permission::CREATE_LOCATION->value);

        return Inertia::render('admin/locations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize(Permission::CREATE_LOCATION->value);

        // Log the request data to debug the address field
        \Log::info('Location creation request data:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|max:2048', // Primary image for background/heading
            'images.*' => 'nullable|image|max:2048', // Additional images
            'is_active' => 'boolean',
        ]);

        // Handle primary image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('locations', 'public');
            $validated['image'] = $path;
        }

        // Log the validated data before creating the location
        \Log::info('Validated data for location creation:', $validated);

        // Create the location
        $location = Location::create($validated);

        // Log the created location to see if the address field is set
        \Log::info('Created location:', $location->toArray());

        // Handle additional images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('locations', 'public');

                // Create a new location image record
                LocationImage::create([
                    'location_id' => $location->id,
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        $this->authorize(Permission::ADMIN_LOCATION->value);

        // Load the additional images
        $location->load('images');

        return Inertia::render('admin/locations/Show', [
            'location' => $location,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        $this->authorize(Permission::UPDATE_LOCATION->value);

        // Load the additional images
        $location->load('images');

        return Inertia::render('admin/locations/Edit', [
            'location' => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $this->authorize(Permission::UPDATE_LOCATION->value);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|max:2048', // Primary image for background/heading
            'images.*' => 'nullable|image|max:2048', // Additional images
            'is_active' => 'boolean',
            'delete_images' => 'nullable|array', // IDs of images to delete
            'delete_images.*' => 'nullable|integer|exists:location_images,id',
        ]);

        // Handle primary image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($location->image) {
                \Storage::disk('public')->delete($location->image);
            }

            $path = $request->file('image')->store('locations', 'public');
            $validated['image'] = $path;
        } else {
            // If no new image is provided, remove the image field from validated data
            // to prevent overwriting the existing image with null
            unset($validated['image']);
        }

        // Update the location
        $location->update($validated);

        // Handle deletion of existing additional images
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = LocationImage::find($imageId);
                if ($image && $image->location_id == $location->id) {
                    // Delete the file
                    \Storage::disk('public')->delete($image->path);
                    // Delete the record
                    $image->delete();
                }
            }
        }

        // Handle additional images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('locations', 'public');

                // Create a new location image record
                LocationImage::create([
                    'location_id' => $location->id,
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $this->authorize(Permission::DELETE_LOCATION->value);

        // Check if the location is being used by any events
        if ($location->events()->count() > 0) {
            return redirect()->route('admin.locations.index')
                ->with('error', 'Cannot delete location because it is being used by events.');
        }

        // Delete the primary image if it exists
        if ($location->image) {
            \Storage::disk('public')->delete($location->image);
        }

        // Delete all related location images
        foreach ($location->images as $image) {
            \Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        // Delete the location
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
