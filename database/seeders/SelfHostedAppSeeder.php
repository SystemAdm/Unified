<?php

namespace Database\Seeders;

use App\Models\SelfHostedApp;
use App\Models\User;
use Illuminate\Database\Seeder;

class SelfHostedAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 random self-hosted apps
        SelfHostedApp::factory(5)->create()->each(function ($app) {
            // Assign 1-3 random users as managers for each app
            $managerIds = User::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $app->managers()->attach($managerIds);
        });

        // Create a specific published app with predefined data
        $dashboardApp = SelfHostedApp::create([
            'name' => 'Analytics Dashboard',
            'description' => 'A comprehensive analytics dashboard for monitoring key performance indicators.',
            'public_link' => 'https://dashboard.example.com',
            'admin_link' => 'https://dashboard.example.com/admin',
            'demo_username' => 'demo_user',
            'demo_password' => 'demo_pass',
            'image' => 'selfhosted/dashboard.jpg',
            'status' => 'published',
            'visibility' => 'user',
        ]);

        // Assign specific users as managers (assuming user IDs 1 and 2 exist)
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->take(2)->get();

        if ($adminUsers->count() > 0) {
            $dashboardApp->managers()->attach($adminUsers->pluck('id'));
        } else {
            // Fallback to first user if no admin users found
            $dashboardApp->managers()->attach(User::first()->id);
        }

        // Create a draft app
        $crmApp = SelfHostedApp::create([
            'name' => 'Customer Relationship Manager',
            'description' => 'A tool for managing customer interactions and relationships.',
            'public_link' => 'https://crm.example.com',
            'admin_link' => 'https://crm.example.com/admin',
            'demo_username' => 'crm_demo',
            'demo_password' => 'crm_pass',
            'image' => 'selfhosted/crm.jpg',
            'status' => 'draft',
            'visibility' => 'admin',
        ]);

        // Assign the first user as manager
        $crmApp->managers()->attach(User::first()->id);
    }
}
