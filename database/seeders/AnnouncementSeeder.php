<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Enum\AnnouncementType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a mix of different announcement types and states
        Announcement::factory(6)->active()->create();
        Announcement::factory(2)->inactive()->create();
        Announcement::factory(2)->expired()->create();

        // Create specific types of announcements
        Announcement::factory(3)->urgent()->create(); // danger type
        Announcement::factory(4)->info()->create(); // info type
        Announcement::factory(2)->active()->ofType(AnnouncementType::WARNING)->create();
        Announcement::factory(1)->active()->ofType(AnnouncementType::PRIMARY)->create();

        // Create some additional random announcements
        Announcement::factory(10)->create();
    }
}
