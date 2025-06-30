<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the organizations.
     */
    public function index(): Response
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        $organizations = Organization::withCount('users')->get();

        return Inertia::render('admin/organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create(): Response
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        return Inertia::render('admin/organizations/Create');
    }

    /**
     * Store a newly created organization in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organization', 'name')],
        ]);

        Organization::create([
            'name' => $request->name,
        ]);

        return Redirect::route('admin.organizations.index')
            ->with('message', 'Organization created successfully.');
    }

    /**
     * Show the form for editing the specified organization.
     */
    public function edit(Organization $organization): Response
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        $organization->load(['users']);

        return Inertia::render('admin/organizations/Edit', [
            'organization' => $organization,
            'users' => $organization->users,
        ]);
    }

    /**
     * Update the specified organization in storage.
     */
    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organization', 'name')->ignore($organization->id)],
        ]);

        $organization->update([
            'name' => $request->name,
        ]);

        return Redirect::route('admin.organizations.index')
            ->with('message', 'Organization updated successfully.');
    }

    /**
     * Remove the specified organization from storage.
     */
    public function destroy(Organization $organization): RedirectResponse
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        // Check if organization has users
        if ($organization->users()->count() > 0) {
            return Redirect::route('admin.organizations.index')
                ->with('error', 'Cannot delete organization with users. Please remove all users first.');
        }

        $organization->delete();

        return Redirect::route('admin.organizations.index')
            ->with('message', 'Organization deleted successfully.');
    }
}
