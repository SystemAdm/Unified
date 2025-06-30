<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission as PermissionModel;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index(): Response
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        return Inertia::render('admin/permissions/Index', [
            'permissions' => PermissionModel::all()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'roles' => $permission->roles->pluck('name'),
                ];
            }),
        ]);
    }

    /**
     * Show details for a specific permission.
     */
    public function show(PermissionModel $permission): Response
    {
        $this->authorize(Permission::ADMIN_ORGANIZATION->value);

        return Inertia::render('admin/permissions/Show', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'roles' => $permission->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
                'users' => $permission->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                }),
            ],
        ]);
    }
}
