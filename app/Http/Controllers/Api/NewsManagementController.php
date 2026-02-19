<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Models\NewsSource;
use App\Services\NewsAggregatorService;

class NewsManagementController extends Controller
{
    /**
     * Trigger news fetching from all sources
     * Can be called from external cron jobs
     */
    public function triggerFetch(Request $request)
    {
        // Verify request (optional: add token-based authentication)
        if ($request->has('token')) {
            $token = $request->input('token');
            if ($token !== env('CRON_TOKEN', 'secret-token')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        try {
            // Ensure default sources exist (production safety)
            if (NewsSource::count() === 0) {
                $defaults = NewsAggregatorService::getDefaultSources();
                foreach ($defaults as $source) {
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

            // Run the fetch command
            Artisan::call('news:fetch', ['--all' => true]);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'News fetching triggered successfully',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update trending topics
     */
    public function updateTrending(Request $request)
    {
        if ($request->has('token')) {
            $token = $request->input('token');
            if ($token !== env('CRON_TOKEN', 'secret-token')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        try {
            Artisan::call('news:update-trending');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Trending topics updated successfully',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate analytics
     */
    public function generateAnalytics(Request $request)
    {
        if ($request->has('token')) {
            $token = $request->input('token');
            if ($token !== env('CRON_TOKEN', 'secret-token')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        try {
            Artisan::call('analytics:generate', ['--period' => 'day']);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Analytics generated successfully',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
