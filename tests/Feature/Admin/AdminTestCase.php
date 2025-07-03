<?php

namespace Tests\Feature\Admin;

use App\Enum\Permission;
use App\Enum\Role;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class AdminTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the roles and permissions
        $this->seed(RoleAndPermissionSeeder::class);
    }
}
