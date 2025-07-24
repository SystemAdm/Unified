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

        // Create some additional random banners
        Banner::factory(8)->create();
    }
}
