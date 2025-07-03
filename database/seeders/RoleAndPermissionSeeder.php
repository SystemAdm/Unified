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
                    // ORGANIZATION
                    Permission::SHOW_ORGANIZATION->value,
                    Permission::INDEX_ORGANIZATION->value,
                    Permission::CREATE_ORGANIZATION->value,
                    Permission::UPDATE_ORGANIZATION->value,
                    Permission::DELETE_ORGANIZATION->value,
                    Permission::ADMIN_ORGANIZATION->value,
                    // USER
                    Permission::SHOW_USER->value,
                    Permission::INDEX_USER->value,
                    Permission::CREATE_USER->value,
                    Permission::UPDATE_USER->value,
                    Permission::DELETE_USER->value,
                    Permission::ADMIN_USER->value,
                    // EVENT
                    Permission::SHOW_EVENT->value,
                    Permission::INDEX_EVENT->value,
                    Permission::CREATE_EVENT->value,
                    Permission::UPDATE_EVENT->value,
                    Permission::DELETE_EVENT->value,
                    Permission::ADMIN_EVENT->value,
                    // LOCATION
                    Permission::SHOW_LOCATION->value,
                    Permission::INDEX_LOCATION->value,
                    Permission::CREATE_LOCATION->value,
                    Permission::UPDATE_LOCATION->value,
                    Permission::DELETE_LOCATION->value,
                    Permission::ADMIN_LOCATION->value,
                    // EMAIL
                    Permission::SHOW_EMAIL->value,
                    Permission::INDEX_EMAIL->value,
                    Permission::CREATE_EMAIL->value,
                    Permission::UPDATE_EMAIL->value,
                    Permission::DELETE_EMAIL->value,
                    Permission::ADMIN_EMAIL->value,
                    // PERMISSION
                    Permission::SHOW_PERMISSION->value,
                    Permission::INDEX_PERMISSION->value,
                    Permission::CREATE_PERMISSION->value,
                    Permission::UPDATE_PERMISSION->value,
                    Permission::DELETE_PERMISSION->value,
                    Permission::ADMIN_PERMISSION->value,
                    // ROLE
                    Permission::SHOW_ROLE->value,
                    Permission::INDEX_ROLE->value,
                    Permission::CREATE_ROLE->value,
                    Permission::UPDATE_ROLE->value,
                    Permission::DELETE_ROLE->value,
                    Permission::ADMIN_ROLE->value,
                    // PHONE
                    Permission::SHOW_PHONE->value,
                    Permission::INDEX_PHONE->value,
                    Permission::CREATE_PHONE->value,
                    Permission::UPDATE_PHONE->value,
                    Permission::DELETE_PHONE->value,
                    Permission::ADMIN_PHONE->value,
                ];
                break;

            case RoleEnum::MODERATOR->value:
            case RoleEnum::GUARDIAN->value:
            case RoleEnum::MEMBER->value:
            case RoleEnum::GUEST->value:
                $permissions = [
                    Permission::INDEX_ORGANIZATION->value,
                    Permission::SHOW_ORGANIZATION->value,
                    Permission::INDEX_LOCATION->value,
                    Permission::SHOW_LOCATION->value,
                    Permission::INDEX_EVENT->value,
                    Permission::SHOW_EVENT->value,
                    Permission::JOIN_EVENT->value,
                ];
                break;
        }

        $role->syncPermissions($permissions);
    }
}
