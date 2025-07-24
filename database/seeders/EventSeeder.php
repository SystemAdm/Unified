<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::factory()->create([
            'title' => 'Friday Event',
            'description' => 'This is a description of the event',
            'start_date' => Carbon::create('next friday at 18:00'),
            'end_date' => Carbon::create('next friday at 22:00'),
            'signup_start_date' => Carbon::now(),
            'signup_end_date'  => Carbon::create('next friday at 22:00'),

            'location_id' => 1,
            'has_signup' => true,
            'seats' => 20,
            'min_age' => 16,
            'class_restriction' => null,
            'restriction' => 'everyone',
            'is_cancelled' => false,
            'status' => 'published',
        ]);
        Event::factory(30)->create();
    }
}
