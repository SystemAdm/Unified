<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Enum\AnnouncementType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a mix of different banner types and states
        Banner::factory(5)->active()->create();
        Banner::factory(3)->inactive()->create();
        Banner::factory(2)->expired()->create();

        // Create specific types of banners
        Banner::factory(2)->active()->ofType(AnnouncementType::DANGER)->create();
        Banner::factory(3)->active()->ofType(AnnouncementType::INFO)->create();
        Banner::factory(2)->active()->ofType(AnnouncementType::WARNING)->create();

        // Create recurring banners for testing relative day labels
        Banner::factory()->forToday()->recurring()->ofType(AnnouncementType::INFO)
            ->create([
                'title' => 'Today\'s Recurring Banner',
                'description' => 'This banner recurs annually on this date'
            ]);

        Banner::factory()->forTomorrow()->recurring()->ofType(AnnouncementType::PRIMARY)
            ->create([
                'title' => 'Tomorrow\'s Recurring Banner',
                'description' => 'This banner recurs annually starting tomorrow'
            ]);

        Banner::factory()->forYesterday()->recurring()->ofType(AnnouncementType::WARNING)
            ->create([
                'title' => 'Yesterday\'s Recurring Banner',
                'description' => 'This banner recurs annually starting from yesterday'
            ]);

        // Create non-recurring banners for specific days
        Banner::factory()->forToday()->ofType(AnnouncementType::INFO)
            ->create([
                'title' => 'Today\'s One-time Banner',
                'description' => 'This is a one-time banner for today'
            ]);

        Banner::factory()->forTomorrow()->ofType(AnnouncementType::PRIMARY)
            ->create([
                'title' => 'Tomorrow\'s One-time Banner',
                'description' => 'This is a one-time banner for tomorrow'
            ]);

        Banner::factory()->forYesterday()->ofType(AnnouncementType::WARNING)
            ->create([
                'title' => 'Yesterday\'s One-time Banner',
                'description' => 'This is a one-time banner from yesterday'
            ]);

        // Create some additional random banners
        Banner::factory(8)->create();
    }
}
