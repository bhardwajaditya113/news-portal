<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AggregatedNews;
use App\Models\NewsSource;
use Illuminate\Http\Request;

class LiveFeedController extends Controller
{
    /**
     * Server-Sent Events stream for frontend live updates
     */
    public function stream()
    {
        return response()->stream(function () {
            $lastId = 0;
            
            while (true) {
                // Get new articles since last check
                $query = AggregatedNews::with('source')
                    ->orderBy('id', 'desc')
                    ->limit(10);
                
                if ($lastId > 0) {
                    $query->where('id', '>', $lastId);
                }
                
                $articles = $query->get();
                
                if ($articles->isNotEmpty()) {
                    $lastId = $articles->first()->id;
                    
                    $data = $articles->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'source' => $item->source?->name ?? 'Unknown',
                            'source_slug' => $item->source?->slug,
                            'image' => $item->image_url,
                            'url' => $item->original_url,
                            'published_at' => $item->published_at?->diffForHumans() ?? 'Just now',
                            'is_breaking' => $item->is_breaking,
                            'description' => \Str::limit($item->description, 120),
                        ];
                    });
                    
                    echo "event: news\n";
                    echo "data: " . json_encode($data) . "\n\n";
                }
                
                // Send heartbeat
                echo "event: heartbeat\n";
                echo "data: " . json_encode(['time' => now()->toISOString(), 'count' => AggregatedNews::count()]) . "\n\n";
                
                ob_flush();
                flush();
                
                if (connection_aborted()) {
                    break;
                }
                
                sleep(2); // Check every 2 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
    
    /**
     * Get latest news via AJAX
     */
    public function latest(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        $limit = $request->get('limit', 20);
        
        $query = AggregatedNews::with('source')
            ->orderBy('fetched_at', 'desc');
        
        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }
        
        $articles = $query->limit($limit)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'source' => $item->source?->name ?? 'Unknown',
                'source_slug' => $item->source?->slug,
                'source_logo' => $item->source?->logo,
                'image' => $item->image_url,
                'url' => $item->original_url,
                'published_at' => $item->published_at?->diffForHumans() ?? 'Just now',
                'fetched_at' => $item->fetched_at?->diffForHumans() ?? 'Just now',
                'is_breaking' => $item->is_breaking,
                'is_featured' => $item->is_featured,
                'description' => \Str::limit($item->description, 150),
            ];
        });
        
        return response()->json([
            'articles' => $articles,
            'total' => AggregatedNews::count(),
            'breaking' => AggregatedNews::where('is_breaking', true)->count(),
        ]);
    }
    
    /**
     * Get breaking news
     */
    public function breaking()
    {
        $articles = AggregatedNews::with('source')
            ->where('is_breaking', true)
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'source' => $item->source?->name ?? 'Unknown',
                    'url' => $item->original_url,
                    'published_at' => $item->published_at?->diffForHumans() ?? 'Just now',
                ];
            });
        
        return response()->json($articles);
    }
    
    /**
     * Get ticker news
     */
    public function ticker()
    {
        $articles = AggregatedNews::with('source')
            ->orderBy('fetched_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'source' => $item->source?->name ?? 'Unknown',
                    'url' => $item->original_url,
                    'is_breaking' => $item->is_breaking,
                ];
            });
        
        return response()->json($articles);
    }
}
