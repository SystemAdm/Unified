<?php

namespace App\Http\Controllers\Admin;

use App\Enum\AnnouncementType;
use App\Enum\Access;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/banners/Index', compact(['banners']));
    }

    public function create()
    {
        $types = collect(AnnouncementType::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        $accessTypes = collect(Access::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        $roles = collect(Role::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        return Inertia::render('admin/banners/Create', compact(['types', 'accessTypes', 'roles']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string',
            'from_datetime' => 'required|date',
            'to_datetime' => 'required|date|after:from_datetime',
            'is_published' => 'boolean',
            'visible_to_access' => 'nullable|array',
            'visible_to_role' => 'nullable|array',
            'link_norwegian' => 'nullable|url|max:255',
            'link_english' => 'nullable|url|max:255',
        ]);

        // Ensure is_published defaults to false if not provided
        $validated['is_published'] = $validated['is_published'] ?? false;

        Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner created successfully.');
    }

    public function show(Banner $banner)
    {
        return Inertia::render('admin/banners/Show', [
            'banner' => $banner
        ]);
    }

    public function edit(Banner $banner)
    {
        $types = collect(AnnouncementType::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        $accessTypes = collect(Access::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        $roles = collect(Role::cases())->map(fn($case) => [
            'name' => ucfirst($case->value),
            'value' => $case->value
        ])->toArray();

        return Inertia::render('admin/banners/Edit', [
            'banner' => $banner,
            'types' => $types,
            'accessTypes' => $accessTypes,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string',
            'from_datetime' => 'required|date',
            'to_datetime' => 'required|date|after:from_datetime',
            'is_published' => 'boolean',
            'visible_to_access' => 'nullable|array',
            'visible_to_role' => 'nullable|array',
            'link_norwegian' => 'nullable|url|max:255',
            'link_english' => 'nullable|url|max:255',
        ]);

        // Ensure is_published defaults to current value if not provided
        $validated['is_published'] = $validated['is_published'] ?? $banner->is_published;

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully.');
    }
}
