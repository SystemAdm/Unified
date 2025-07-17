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
        $organizations = Organization::withCount('users')
            ->orderBy('name')
            ->paginate(9);

        return Inertia::render('organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization)
    {
        $organization->load(['users']);

        return Inertia::render('organizations/Show', [
            'organization' => $organization,
        ]);
    }
}
