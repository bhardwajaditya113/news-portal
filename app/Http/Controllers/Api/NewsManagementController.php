<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controller;

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
