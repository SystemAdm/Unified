<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\Email;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RoleAndPermissionSeeder::class);

        // Create an organization
        $organization = Organization::create([
            'name' => 'Spillhuset',
        ]);

        $oddUser = User::factory()->create([
            'given_name' => 'Odd-Erik',
            'family_name' => 'Jovang',
            'birthday' => '1982-02-11',
        ]);

        $tesUser = User::factory()->withoutPassword()->create(['given_name' => 'Test', 'family_name' => 'User']);
        $gutUser = User::factory()->withoutPassword()->create(['given_name' => 'Guest', 'family_name' => 'User']);
        $priUser = User::factory()->create(['password' => 'password']);
        $secUser = User::factory()->create(['password' => 'password']);;

        // CREATE EMAIL
        $priEmail = Email::factory()->create(['address' => 'test@example.com']);
        $oddEmail = Email::factory()->create(['address' => 'odd-erik@spillhuset.com']);
        $secEmail = Email::factory()->create(['address' => 'jovang@gmail.com']);

        // CREATE PHONE
        $priPhone = Phone::create(['phone_number' => '98218519']);
        $gutPhone = Phone::create(['phone_number' => '99773399']);

        $locHelset = Location::factory()->create([
            'name' => 'Helset',
            'address' => 'Skollerudveien 5',
            'city' => 'Bærums Verk',
            'postal_code' => '1353',
            'country' => 'Norway',
            'latitude' => '59.616667',
            'longitude' => '10.716667',
            'description' => 'Helset Fritidssenter, ligger i første etasje på Helset Hallen, med inngang på siden av bygget.',
            'state' => 'Akershus',
            'is_active' => true,
        ]);
        $locSandvika = Location::factory()->create([
            'name' => 'Løkketangen',
            'address' => 'Løkketange 6-14',
            'city' => 'Sandvika',
            'postal_code' => '1337',
            'country' => 'Norway',
            'latitude' => '59.616667',
            'longitude' => '10.716667',
            'state' =>'Akershus',
            'description' => 'Ny oppussede lokaler i Sandvika 100m fra bussterminalen, åpner 2026.',
            'is_active' => false,
        ]);

        // SET USER EMAIL
        $priUser->emails()->attach($priEmail);
        $priUser->setPrimaryEmail($priEmail);;

        $secUser->emails()->attach($priEmail);

        $oddUser->emails()->attach($oddEmail);
        $oddUser->emails()->attach($secEmail);
        $oddUser->markEmailAsVerified($oddEmail);
        $oddUser->markEmailAsVerified($priEmail);
        $oddUser->markEmailAsVerified($secEmail);
        $oddUser->setPrimaryEmail($oddEmail);

        // SET USER ORGANIZATION
        $oddUser->organizations()->attach($organization, ['is_chairman' => true, 'is_board' => true, 'is_contact' => true]);
        $priUser->organizations()->attach($organization);
        $gutUser->organizations()->attach($organization);

        // SET USER PHONE
        $oddUser->phones()->attach($priPhone);
        $tesUser->phones()->attach($priPhone);
        $oddUser->markPhoneAsVerified($priPhone);
        $oddUser->setPrimaryPhone($priPhone);
        $gutUser->phones()->attach($gutPhone);

        // SET ROLE
        $oddUser->assignRole(Role::OWNER->value);
        $tesUser->assignRole(Role::GUEST->value);

        // SAVE && REFRESH
        $oddUser->save();
        $oddUser->refresh();

        $this->call(EventSeeder::class);
        $this->call(BannerSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(GameSeeder::class);
        $this->call(WishlistSeeder::class);
        $this->call(SelfHostedAppSeeder::class);
    }
}
