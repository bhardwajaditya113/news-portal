<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RealtimeFeedService;
use App\Models\NewsSource;
use App\Models\AggregatedNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RealtimeFeedController extends Controller
{
    protected RealtimeFeedService $feedService;
    
    public function __construct(RealtimeFeedService $feedService)
    {
        $this->feedService = $feedService;
    }
    
    /**
     * Real-time feed dashboard
     */
    public function index()
    {
        $status = RealtimeFeedService::getStatus();
        $stats = RealtimeFeedService::getStats();
        $sources = NewsSource::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
        
        $recentNews = AggregatedNews::with('source')
            ->orderBy('fetched_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('admin.realtime-feed.index', compact('status', 'stats', 'sources', 'recentNews'));
    }
    
    /**
     * Start real-time feed
     */
    public function start()
    {
        RealtimeFeedService::start();
        
        return response()->json([
            'success' => true,
            'message' => 'Real-time feed started',
            'status' => RealtimeFeedService::getStatus(),
        ]);
    }
    
    /**
     * Stop real-time feed
     */
    public function stop()
    {
        RealtimeFeedService::stop();
        
        return response()->json([
            'success' => true,
            'message' => 'Real-time feed stopped',
            'status' => RealtimeFeedService::getStatus(),
        ]);
    }
    
    /**
     * Get current status (for AJAX polling)
     */
    public function status()
    {
        return response()->json([
            'status' => RealtimeFeedService::getStatus(),
            'stats' => RealtimeFeedService::getStats(),
        ]);
    }
    
    /**
     * Perform single fetch cycle (called by frontend)
     */
    public function cycle()
    {
        $result = $this->feedService->cycle();
        
        return response()->json($result);
    }
    
    /**
     * Server-Sent Events stream for real-time updates
     */
    public function stream()
    {
        return response()->stream(function () {
            // Send headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');
            
            $lastEventId = 0;
            
            while (true) {
                $status = RealtimeFeedService::getStatus();
                
                if (!$status['running']) {
                    echo "event: stopped\n";
                    echo "data: " . json_encode(['message' => 'Feed stopped']) . "\n\n";
                    ob_flush();
                    flush();
                    sleep(2);
                    continue;
                }
                
                // Perform a fetch cycle
                $feedService = new RealtimeFeedService();
                $result = $feedService->cycle();
                
                // Send status update
                echo "event: status\n";
                echo "data: " . json_encode([
                    'status' => RealtimeFeedService::getStatus(),
                    'stats' => RealtimeFeedService::getStats(),
                ]) . "\n\n";
                
                // Send fetch result
                echo "event: fetch\n";
                echo "data: " . json_encode($result) . "\n\n";
                
                // Send new articles if any
                if (!empty($result['new_articles'])) {
                    echo "event: articles\n";
                    echo "data: " . json_encode($result['new_articles']) . "\n\n";
                }
                
                ob_flush();
                flush();
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
                
                sleep(1); // 1 second between fetches
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
    
    /**
     * Get latest news (for AJAX)
     */
    public function latestNews()
    {
        $news = AggregatedNews::with('source')
            ->orderBy('fetched_at', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'source' => $item->source?->name ?? 'Unknown',
                    'source_logo' => $item->source?->logo,
                    'image' => $item->image_url,
                    'url' => $item->original_url,
                    'published_at' => $item->published_at?->diffForHumans() ?? 'Just now',
                    'fetched_at' => $item->fetched_at?->diffForHumans() ?? 'Just now',
                    'is_breaking' => $item->is_breaking,
                    'description' => \Str::limit($item->description, 150),
                ];
            });
        
        return response()->json($news);
    }
    
    /**
     * Force fetch from specific source
     */
    public function fetchSource(NewsSource $source)
    {
        $result = $this->feedService->fetchFromSource($source);
        
        return response()->json($result);
    }
    
    /**
     * Toggle source active status
     */
    public function toggleSource(NewsSource $source)
    {
        $source->update(['is_active' => !$source->is_active]);
        
        // Reinitialize queue
        RealtimeFeedService::initializeQueue();
        
        return response()->json([
            'success' => true,
            'is_active' => $source->is_active,
        ]);
    }
    
    /**
     * Get failed sources
     */
    public function failedSources()
    {
        $stats = RealtimeFeedService::getStats();
        
        return response()->json($stats['sources_failed'] ?? []);
    }
    
    /**
     * Clear all cached data and reset
     */
    public function reset()
    {
        Cache::forget(RealtimeFeedService::CACHE_KEY_STATUS);
        Cache::forget(RealtimeFeedService::CACHE_KEY_STATS);
        Cache::forget(RealtimeFeedService::CACHE_KEY_LATEST);
        Cache::forget(RealtimeFeedService::CACHE_KEY_QUEUE);
        
        return response()->json([
            'success' => true,
            'message' => 'Feed service reset',
        ]);
    }
}
