<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelfHostedApp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SelfHostedAppController extends Controller
{
    /**
     * Get available images from the selfhosted storage directory
     */
    private function getAvailableImages()
    {
        $images = [];
        $files = Storage::disk('public')->files('selfhosted');

        foreach ($files as $file) {
            // Only include image files
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = [
                    'path' => $file,
                    'url' => '/storage/' . $file,
                    'name' => basename($file),
                    'size' => Storage::disk('public')->size($file),
                ];
            }
        }

        // Sort by name
        usort($images, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $images;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $apps = SelfHostedApp::with('managers')->orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/selfhostedapp/Index', compact(['apps']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availableImages = $this->getAvailableImages();
        $users = User::select('id', 'name', 'email')->get();
        return Inertia::render('admin/selfhostedapp/Create', compact(['availableImages', 'users']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'public_link' => 'nullable|url|max:255',
            'admin_link' => 'nullable|url|max:255',
            'demo_username' => 'nullable|string|max:255',
            'demo_password' => 'nullable|string|max:255',
            'status' => 'required|in:published,draft',
            'visibility' => 'required|in:admin,user',
            'image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
            'selected_image' => 'nullable|string', // Allow selection of existing images
            'manager_ids' => 'required|array|min:1',
            'manager_ids.*' => 'exists:users,id',
        ]);

        // Handle image - either upload new or use selected existing image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('selfhosted', 'public');
            $validated['image'] = $path;
        } elseif ($request->filled('selected_image')) {
            // Validate that the selected image exists in the selfhosted directory
            if (Storage::disk('public')->exists($request->selected_image) &&
                str_starts_with($request->selected_image, 'selfhosted/')) {
                $validated['image'] = $request->selected_image;
            }
        }

        // Remove selected_image and manager_ids from validated data as they're not database fields
        $managerIds = $validated['manager_ids'] ?? [];
        unset($validated['selected_image'], $validated['manager_ids']);

        // Create the app
        $app = SelfHostedApp::create($validated);

        // Attach managers
        $app->managers()->attach($managerIds);

        return redirect()->route('admin.selfhostedapp.index')
            ->with('success', 'Self-hosted app created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SelfHostedApp $selfhostedapp)
    {
        $selfhostedapp->load('managers');
        return Inertia::render('admin/selfhostedapp/Show', [
            'app' => $selfhostedapp
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SelfHostedApp $selfhostedapp)
    {
        $selfhostedapp->load('managers');
        $availableImages = $this->getAvailableImages();
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('admin/selfhostedapp/Edit', [
            'app' => $selfhostedapp,
            'availableImages' => $availableImages,
            'users' => $users,
            'managerIds' => $selfhostedapp->managers->pluck('id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SelfHostedApp $selfhostedapp)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'public_link' => 'nullable|url|max:255',
            'admin_link' => 'nullable|url|max:255',
            'demo_username' => 'nullable|string|max:255',
            'demo_password' => 'nullable|string|max:255',
            'status' => 'required|in:published,draft',
            'visibility' => 'required|in:admin,user',
            'image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
            'selected_image' => 'nullable|string', // Allow selection of existing images
            'manager_ids' => 'required|array|min:1',
            'manager_ids.*' => 'exists:users,id',
        ]);

        // Handle image - either upload new or use selected existing image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($selfhostedapp->image) {
                Storage::disk('public')->delete($selfhostedapp->image);
            }

            $path = $request->file('image')->store('selfhosted', 'public');
            $validated['image'] = $path;
        } elseif ($request->filled('selected_image')) {
            // Validate that the selected image exists in the selfhosted directory
            if (Storage::disk('public')->exists($request->selected_image) &&
                str_starts_with($request->selected_image, 'selfhosted/')) {

                // Only delete old image if we're changing to a different one
                if ($selfhostedapp->image && $selfhostedapp->image !== $request->selected_image) {
                    Storage::disk('public')->delete($selfhostedapp->image);
                }

                $validated['image'] = $request->selected_image;
            }
        } else {
            // If no new image is uploaded and no existing image is selected,
            // preserve the current image
            $validated['image'] = $selfhostedapp->image;
        }

        // Remove selected_image and manager_ids from validated data as they're not database fields
        $managerIds = $validated['manager_ids'] ?? [];
        unset($validated['selected_image'], $validated['manager_ids']);

        // Update the app
        $selfhostedapp->update($validated);

        // Sync managers
        $selfhostedapp->managers()->sync($managerIds);

        return redirect()->route('admin.selfhostedapp.index')
            ->with('success', 'Self-hosted app updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SelfHostedApp $selfhostedapp)
    {
        // Delete image if exists
        if ($selfhostedapp->image) {
            Storage::disk('public')->delete($selfhostedapp->image);
        }

        // Detach all managers
        $selfhostedapp->managers()->detach();

        // Delete the app
        $selfhostedapp->delete();

        return redirect()->route('admin.selfhostedapp.index')
            ->with('success', 'Self-hosted app deleted successfully.');
    }

    /**
     * Get users for the manager selection
     */
    public function getUsers(Request $request)
    {
        $search = $request->input('search', '');
        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
