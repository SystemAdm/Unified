<?php

namespace App\Http\Controllers\Admin;

use App\Enum\AnnouncementType;
use App\Enum\Access;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/announcements/Index', compact(['announcements']));
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

        return Inertia::render('admin/announcements/Create', compact(['types', 'accessTypes', 'roles']));
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
        ]);

        // Ensure is_published defaults to false if not provided
        $validated['is_published'] = $validated['is_published'] ?? false;

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement)
    {
        return Inertia::render('admin/announcements/Show', [
            'announcement' => $announcement
        ]);
    }

    public function edit(Announcement $announcement)
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

        return Inertia::render('admin/announcements/Edit', [
            'announcement' => $announcement,
            'types' => $types,
            'accessTypes' => $accessTypes,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, Announcement $announcement)
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
        ]);

        // Ensure is_published defaults to current value if not provided
        $validated['is_published'] = $validated['is_published'] ?? $announcement->is_published;

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
