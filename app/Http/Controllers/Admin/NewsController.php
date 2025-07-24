<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Get available images from the news storage directory
     */
    private function getAvailableImages()
    {
        $images = [];
        $files = Storage::disk('public')->files('news');

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

    public function index()
    {
        $news = News::with('author')->orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/news/Index', compact(['news']));
    }

    public function create()
    {
        $roles = collect(Role::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        $availableImages = $this->getAvailableImages();

        return Inertia::render('admin/news/Create', compact(['roles', 'availableImages']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'visible_to_role' => 'nullable|array',
            'featured_image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
            'selected_image' => 'nullable|string', // Allow selection of existing images
        ]);

        // Set author from authenticated user
        $validated['author_id'] = Auth::id();

        // Role-based publishing logic: only admins can publish, moderators can only create drafts
        $user = Auth::user();
        if ($user->hasRole(Role::ADMIN->value)) {
            // Admin can set is_published as requested
            $isPublished = $validated['is_published'] ?? false;
            $validated['is_published'] = $isPublished;

            // If is_published is true and published_at is null or in the future,
            // set published_at to the current date
            if ($isPublished) {
                $publishedAt = $request->input('published_at');
                if ($publishedAt === null || $publishedAt > now()) {
                    $validated['published_at'] = now();
                }
            }
        } else {
            // Moderators and other roles can only create drafts
            $validated['is_published'] = false;
        }

        // Handle featured image - either upload new or use selected existing image
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('news', 'public');
            $validated['featured_image'] = $path;
        } elseif ($request->filled('selected_image')) {
            // Validate that the selected image exists in the news directory
            if (Storage::disk('public')->exists($request->selected_image) &&
                str_starts_with($request->selected_image, 'news/')) {
                $validated['featured_image'] = $request->selected_image;
            }
        }

        // Remove selected_image from validated data as it's not a database field
        unset($validated['selected_image']);

        News::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'News article created successfully.');
    }

    public function show(News $news)
    {
        $news->load('author');
        return Inertia::render('admin/news/Show', [
            'news' => $news
        ]);
    }

    public function edit(News $news)
    {
        $roles = collect(Role::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        $availableImages = $this->getAvailableImages();

        return Inertia::render('admin/news/Edit', [
            'news' => $news,
            'roles' => $roles,
            'availableImages' => $availableImages
        ]);
    }

    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'visible_to_role' => 'nullable|array',
            'featured_image' => 'nullable|image|max:2048', // Allow image uploads up to 2MB
            'selected_image' => 'nullable|string', // Allow selection of existing images
        ]);

        // Role-based publishing logic: only admins can publish, moderators can only create drafts
        $user = Auth::user();
        if ($user->hasRole(Role::ADMIN->value)) {
            // Admin can set is_published as requested
            // When a checkbox is unchecked, the field might not be included in the request at all
            // or it might be sent as an empty string or null
            // We need to check if the field exists in the request and its value
            if ($request->has('is_published')) {
                $newIsPublished = (bool)$request->input('is_published');
            } else {
                // If the field doesn't exist in the request, it means the checkbox was unchecked
                $newIsPublished = false;
            }
            $validated['is_published'] = $newIsPublished;

            // Handle published_at date based on is_published status
            $publishedAt = $request->input('published_at');

            if ($newIsPublished) {
                // If article is being published or is already published
                // and published_at is null or in the future, set it to now
                if ($publishedAt === null || $publishedAt > now()) {
                    $validated['published_at'] = now();
                }
            }
        } else {
            // Moderators and other roles can only create drafts, cannot publish
            $validated['is_published'] = false;
        }

        // Handle featured image - either upload new or use selected existing image
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($news->featured_image) {
                Storage::disk('public')->delete($news->featured_image);
            }

            $path = $request->file('featured_image')->store('news', 'public');
            $validated['featured_image'] = $path;
        } elseif ($request->filled('selected_image')) {
            // Validate that the selected image exists in the news directory
            if (Storage::disk('public')->exists($request->selected_image) &&
                str_starts_with($request->selected_image, 'news/')) {

                // Only delete old image if we're changing to a different one
                if ($news->featured_image && $news->featured_image !== $request->selected_image) {
                    Storage::disk('public')->delete($news->featured_image);
                }

                $validated['featured_image'] = $request->selected_image;
            }
        } else {
            // If no new image is uploaded and no existing image is selected,
            // preserve the current image
            $validated['featured_image'] = $news->featured_image;
        }

        // Remove selected_image from validated data as it's not a database field
        unset($validated['selected_image']);

        // Preserve the existing author_id when updating
        $validated['author_id'] = $news->author_id;

        $news->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'News article updated successfully.');
    }

    public function destroy(News $news)
    {
        // Delete featured image if exists
        if ($news->featured_image) {
            Storage::disk('public')->delete($news->featured_image);
        }

        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'News article deleted successfully.');
    }
}
