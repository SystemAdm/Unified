<?php

namespace Database\Seeders;

use App\Enum\Permission;
use App\Enum\Role as RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Permission::cases() as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission->value]);
        }

        foreach (RoleEnum::cases() as $role) {
            $role = Role::create(['name' => $role->value]);

            $this->syncPermissionToRole($role);
        }
    }

    private function syncPermissionToRole(Role $role): void
    {
        $permissions = [];

        switch ($role->name) {
            case RoleEnum::OWNER->value:
            case RoleEnum::ADMIN->value:
                $permissions = [
                    Permission::INDEX_ORGANIZATION,
                    Permission::CREATE_ORGANIZATION,
                    Permission::UPDATE_ORGANIZATION,
                    Permission::DELETE_ORGANIZATION,
                    Permission::ADMIN_ORGANIZATION,
                    Permission::INDEX_USER,
                    Permission::CREATE_USER,
                    Permission::UPDATE_USER,
                    Permission::DELETE_USER,
                    Permission::ADMIN_USER,
                ];
                break;

            case RoleEnum::MODERATOR->value:
            case RoleEnum::GUARDIAN->value:
            case RoleEnum::MEMBER->value:
            case RoleEnum::GUEST->value:
                $permissions = [
                    Permission::INDEX_ORGANIZATION,
                ];
                break;
        }

        $role->syncPermissions($permissions);
    }
}
