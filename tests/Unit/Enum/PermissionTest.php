<?php

namespace Tests\Unit\Enum;

use App\Enum\Access;
use App\Enum\Models;
use App\Enum\Permission;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    /**
     * Test that permissions can be created by combining Access and Models enums.
     */
    public function test_can_create_permission_from_access_and_model()
    {
        // Test creating a permission for index_user
        $permission = Permission::fromAccessAndModel(Access::INDEX, Models::USER);
        $this->assertEquals(Permission::INDEX_USER, $permission);

        // Test creating a permission for create_organization
        $permission = Permission::fromAccessAndModel(Access::CREATE, Models::ORGANIZATION);
        $this->assertEquals(Permission::CREATE_ORGANIZATION, $permission);

        // Test creating a permission for update_event
        $permission = Permission::fromAccessAndModel(Access::UPDATE, Models::EVENT);
        $this->assertEquals(Permission::UPDATE_EVENT, $permission);

        // Test creating a permission for delete_location
        $permission = Permission::fromAccessAndModel(Access::DELETE, Models::LOCATION);
        $this->assertEquals(Permission::DELETE_LOCATION, $permission);

        // Test creating a permission for admin_event
        $permission = Permission::fromAccessAndModel(Access::ADMIN, Models::EVENT);
        $this->assertEquals(Permission::ADMIN_EVENT, $permission);
    }

    /**
     * Test that SHOW_USER is a valid permission.
     */
    public function test_show_user_is_valid_permission()
    {
        // SHOW_USER should exist in the Permission enum
        $permission = Permission::fromAccessAndModel(Access::SHOW, Models::USER);
        $this->assertEquals(Permission::SHOW_USER, $permission);
    }

    /**
     * Test that permission strings can be created by combining Access and Models enums
     * without requiring the permission to exist in the enum.
     */
    public function test_can_create_permission_string_from_access_and_model()
    {
        // Test creating a permission string for show_user
        $permissionString = Permission::createFromAccessAndModel(Access::SHOW, Models::USER);
        $this->assertEquals('show_user', $permissionString);

        // Test creating a permission string for update_organization
        $permissionString = Permission::createFromAccessAndModel(Access::UPDATE, Models::ORGANIZATION);
        $this->assertEquals('update_organization', $permissionString);

        // Test creating a permission string for delete_event
        $permissionString = Permission::createFromAccessAndModel(Access::DELETE, Models::EVENT);
        $this->assertEquals('delete_event', $permissionString);
    }
}
