<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\Email;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RoleAndPermissionSeeder::class);

        $oddUser = User::factory()->create([
            'given_name' => 'Odd-Erik',
            'family_name' => 'Jovang',
            'birthday' => '1982-02-11',
        ]);

        $primaryUser = User::factory()->create();
        $secondaryUser = User::factory()->create();

        $primaryEmail = Email::factory()->create(['address' => 'test@example.com']);

        $primaryUser->emails()->attach($primaryEmail, ['is_primary' => true]);
        $secondaryUser->emails()->attach($primaryEmail);

        $oddEmail = Email::factory()->create([
            'address' => 'odd-erik@spillhuset.com',
        ]);

        $oddUser->emails()->attach($oddEmail);
        $oddUser->assignRole(Role::OWNER);
    }
}
