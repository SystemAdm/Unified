<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;

class RoleController extends AdminController
{
    /**
     * Constructor to authorize admin access
     */
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_ROLE->value);
    }

    /**
     * Display a listing of the roles.
     */
    public function index(): Response
    {
        $this->authorize(Permission::INDEX_ROLE->value);

        return Inertia::render('admin/roles/Index', [
            'roles' => Role::with('permissions')->paginate(10)->through(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        $this->authorize(Permission::CREATE_ROLE->value);

        return Inertia::render('admin/roles/Create', [
            'permissions' => PermissionModel::all()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize(Permission::CREATE_ROLE->value);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        $permissions = PermissionModel::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);

        return Redirect::route('admin.roles.index');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        $this->authorize(Permission::UPDATE_ROLE->value);

        return Inertia::render('admin/roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('id'),
            ],
            'permissions' => PermissionModel::all()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }),
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize(Permission::UPDATE_ROLE->value);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $permissions = PermissionModel::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);

        return Redirect::route('admin.roles.index');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize(Permission::DELETE_ROLE->value);

        // Don't allow deleting built-in roles
        if (in_array($role->name, array_map(fn($case) => $case->value, \App\Enum\Role::cases()))) {
            return Redirect::route('admin.roles.index')->with('error', 'Cannot delete built-in roles');
        }

        $role->delete();

        return Redirect::route('admin.roles.index');
    }
}
