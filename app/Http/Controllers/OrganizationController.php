<?php

namespace App\Http\Controllers;

use App\Enum\Permission;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the organizations.
     */
    public function index()
    {
        $this->authorize(Permission::INDEX_ORGANIZATION->value);

        $organizations = Organization::withCount('users')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization)
    {
        // Check if the user has the SHOW_ORGANIZATION permission
        $this->authorize(Permission::SHOW_ORGANIZATION->value);

        $organization->load(['users']);

        return Inertia::render('organizations/Show', [
            'organization' => $organization,
        ]);
    }
}
