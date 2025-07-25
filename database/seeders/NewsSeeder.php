<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a mix of different news states
        News::factory(8)->published()->withFeaturedImage()->create();
        News::factory(3)->draft()->create();
        News::factory(2)->scheduled()->withFeaturedImage()->create();

        // Create news with specific characteristics
        News::factory(4)->published()->withAuthor(1)->withFeaturedImage()->create();
        News::factory(3)->published()->withoutFeaturedImage()->withExcerpt()->create();
        News::factory(2)->published()->longArticle()->withFeaturedImage()->create();
        News::factory(3)->published()->shortArticle()->create();

        // Create news without authors (system generated)
        News::factory(2)->published()->withoutAuthor()->withFeaturedImage()->create();

        // Create some additional random news articles
        News::factory(12)->create();
    }
}
