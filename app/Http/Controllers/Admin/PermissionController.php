<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission as PermissionModel;

class PermissionController extends AdminController
{
    /**
     * Constructor to authorize admin access
     */
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_PERMISSION->value);
    }

    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request): Response
    {
        $this->authorize(Permission::INDEX_PERMISSION->value);

        $query = PermissionModel::with('roles')->orderBy('name');

        // Filter by action if provided
        if ($request->has('action') && $request->action) {
            $query->where('name', 'like', $request->action . '_%');
        }

        // Filter by model if provided
        if ($request->has('model') && $request->model) {
            $query->where('name', 'like', '%_' . $request->model);
        }

        // Get all actions and models for the filter dropdowns
        $actions = \App\Enum\Access::cases();
        $models = \App\Enum\Models::cases();

        $permissions = $query->paginate(10);
        return Inertia::render('admin/permissions/Index', [
            'permissions' => $permissions->through(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'roles' => $permission->roles->pluck('name'),
                ];
            }),
            'actions' => collect($actions)->map(fn($action) => $action->value),
            'models' => collect($models)->map(fn($model) => $model->value),
            'filters' => $request->only(['action', 'model']),
        ]);
    }

    /**
     * Show details for a specific permission.
     */
    public function show(PermissionModel $permission): Response
    {
        $this->authorize(Permission::SHOW_PERMISSION->value);

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
