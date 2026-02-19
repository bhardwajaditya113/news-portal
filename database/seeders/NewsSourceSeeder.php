<?php

namespace Database\Seeders;

use App\Models\NewsSource;
use App\Services\NewsAggregatorService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = NewsAggregatorService::getDefaultSources();

        foreach ($sources as $source) {
            NewsSource::updateOrCreate(
                ['slug' => $source['slug']],
                [
                    'name' => $source['name'],
                    'website_url' => $source['website_url'],
                    'rss_feed_url' => $source['rss_feed_url'],
                    'api_type' => $source['api_type'],
                    'api_key' => $source['api_key'] ?? null,
                    'category_mapping' => $source['category_mapping'],
                    'country' => $source['country'],
                    'language' => $source['language'],
                    'priority' => $source['priority'],
                    'credibility_score' => $source['credibility_score'],
                    'fetch_interval' => $source['fetch_interval'] ?? 30,
                    'is_active' => true,
                ]
            );
        }
    }
}
