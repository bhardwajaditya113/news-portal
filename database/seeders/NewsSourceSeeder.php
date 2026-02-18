<?php

namespace Database\Seeders;

use App\Models\NewsSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'BBC News',
                'url' => 'https://www.bbc.com/news',
                'api_endpoint' => 'https://newsapi.org/v2/everything?sources=bbc-news',
                'api_key' => env('NEWS_API_KEY', ''),
                'source_type' => 'rss',
                'fetch_interval' => 30,
                'priority' => 5,
                'is_active' => 1,
                'last_fetched_at' => null,
            ],
            [
                'name' => 'Reuters',
                'url' => 'https://www.reuters.com',
                'api_endpoint' => 'https://newsapi.org/v2/everything?sources=reuters',
                'api_key' => env('NEWS_API_KEY', ''),
                'source_type' => 'rss',
                'fetch_interval' => 30,
                'priority' => 5,
                'is_active' => 1,
                'last_fetched_at' => null,
            ],
            [
                'name' => 'CNN',
                'url' => 'https://www.cnn.com',
                'api_endpoint' => 'https://newsapi.org/v2/everything?sources=cnn',
                'api_key' => env('NEWS_API_KEY', ''),
                'source_type' => 'rss',
                'fetch_interval' => 30,
                'priority' => 4,
                'is_active' => 1,
                'last_fetched_at' => null,
            ],
            [
                'name' => 'The Guardian',
                'url' => 'https://www.theguardian.com',
                'api_endpoint' => 'https://newsapi.org/v2/everything?sources=the-guardian-uk',
                'api_key' => env('NEWS_API_KEY', ''),
                'source_type' => 'rss',
                'fetch_interval' => 45,
                'priority' => 4,
                'is_active' => 1,
                'last_fetched_at' => null,
            ],
            [
                'name' => 'Associated Press',
                'url' => 'https://apnews.com',
                'api_endpoint' => 'https://newsapi.org/v2/everything?sources=associated-press',
                'api_key' => env('NEWS_API_KEY', ''),
                'source_type' => 'rss',
                'fetch_interval' => 30,
                'priority' => 5,
                'is_active' => 1,
                'last_fetched_at' => null,
            ],
        ];

        foreach ($sources as $source) {
            NewsSource::firstOrCreate(
                ['name' => $source['name']],
                $source
            );
        }
    }
}
