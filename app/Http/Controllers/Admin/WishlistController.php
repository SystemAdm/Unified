<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Get available images from the wishlist storage directory
     */
    private function getAvailableImages()
    {
        $images = [];
        $files = Storage::disk('public')->files('wishlist');

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
        $wishlists = Wishlist::orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/wishlist/Index', compact(['wishlists']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availableImages = $this->getAvailableImages();
        return Inertia::render('admin/wishlist/Create', compact(['availableImages']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'cost_per_unit' => 'required|numeric|min:0',
            'count' => 'required|integer|min:1',
            'deadline' => 'nullable|date',
            'image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
            'selected_image' => 'nullable|string', // Allow selection of existing images
        ]);

        // Handle image - either upload new or use selected existing image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('wishlist', 'public');
            $validated['image'] = $path;
        } elseif ($request->filled('selected_image')) {
            // Validate that the selected image exists in the wishlist directory
            if (Storage::disk('public')->exists($request->selected_image) &&
                str_starts_with($request->selected_image, 'wishlist/')) {
                $validated['image'] = $request->selected_image;
            }
        }

        // Remove selected_image from validated data as it's not a database field
        unset($validated['selected_image']);

        Wishlist::create($validated);

        return redirect()->route('admin.wishlist.index')
            ->with('success', 'Wishlist item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Wishlist $wishlist)
    {
        $wishlist->load('payments.user');
        return Inertia::render('admin/wishlist/Show', [
            'wishlist' => $wishlist
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wishlist $wishlist)
    {
        $availableImages = $this->getAvailableImages();
        return Inertia::render('admin/wishlist/Edit', [
            'wishlist' => $wishlist,
            'availableImages' => $availableImages
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wishlist $wishlist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'cost_per_unit' => 'required|numeric|min:0',
            'count' => 'required|integer|min:1',
            'deadline' => 'nullable|date',
            'image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
            'selected_image' => 'nullable|string', // Allow selection of existing images
        ]);

        // Handle image - either upload new or use selected existing image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($wishlist->image) {
                Storage::disk('public')->delete($wishlist->image);
            }

            $path = $request->file('image')->store('wishlist', 'public');
            $validated['image'] = $path;
        } elseif ($request->filled('selected_image')) {
            // Validate that the selected image exists in the wishlist directory
            if (Storage::disk('public')->exists($request->selected_image) &&
                str_starts_with($request->selected_image, 'wishlist/')) {

                // Only delete old image if we're changing to a different one
                if ($wishlist->image && $wishlist->image !== $request->selected_image) {
                    Storage::disk('public')->delete($wishlist->image);
                }

                $validated['image'] = $request->selected_image;
            }
        } else {
            // If no new image is uploaded and no existing image is selected,
            // preserve the current image
            $validated['image'] = $wishlist->image;
        }

        // Remove selected_image from validated data as it's not a database field
        unset($validated['selected_image']);

        $wishlist->update($validated);

        return redirect()->route('admin.wishlist.index')
            ->with('success', 'Wishlist item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wishlist $wishlist)
    {
        // Delete image if exists
        if ($wishlist->image) {
            Storage::disk('public')->delete($wishlist->image);
        }

        $wishlist->delete();

        return redirect()->route('admin.wishlist.index')
            ->with('success', 'Wishlist item deleted successfully.');
    }

    /**
     * Record a payment for a wishlist item
     */
    public function recordPayment(Request $request, Wishlist $wishlist)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'count' => 'required|integer|min:1|max:' . $wishlist->count,
        ]);

        try {
            DB::beginTransaction();

            // Create the payment record
            WishlistPayment::create([
                'user_id' => $validated['user_id'],
                'wishlist_id' => $wishlist->id,
                'count' => $validated['count'],
                'payment_timestamp' => now(),
            ]);

            // Reduce the count of the wishlist item
            $wishlist->update([
                'count' => $wishlist->count - $validated['count'],
            ]);

            DB::commit();

            return redirect()->route('admin.wishlist.show', $wishlist)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Get users for the payment form
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
