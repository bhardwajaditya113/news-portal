<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AggregatedNews;
use App\Models\Analytics;
use App\Models\News;
use App\Models\NewsSource;
use App\Models\TrendingTopic;
use App\Services\NewsAggregatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NewsApiController extends Controller
{
    protected $aggregatorService;

    public function __construct(NewsAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Get breaking news (real-time)
     */
    public function breakingNews()
    {
        $breakingNews = Cache::remember('api.breaking_news', 60, function () {
            // Internal breaking news
            $internal = News::where('is_breaking_news', 1)
                ->activeEntries()
                ->orderBy('id', 'DESC')
                ->take(5)
                ->get()
                ->map(function ($news) {
                    return [
                        'id' => $news->id,
                        'type' => 'internal',
                        'title' => $news->title,
                        'url' => route('news-details', $news->slug),
                        'image' => asset($news->image),
                        'category' => $news->category->name ?? null,
                        'published_at' => $news->created_at->toIso8601String(),
                        'time_ago' => $news->created_at->diffForHumans(),
                    ];
                });

            // Aggregated breaking news
            $aggregated = AggregatedNews::with('source')
                ->where('is_breaking', true)
                ->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($news) {
                    return [
                        'id' => $news->id,
                        'type' => 'aggregated',
                        'title' => $news->title,
                        'url' => $news->original_url,
                        'image' => $news->image,
                        'source' => $news->source->name ?? null,
                        'source_logo' => $news->source->logo_url ?? null,
                        'category' => $news->category->name ?? null,
                        'published_at' => $news->published_at->toIso8601String(),
                        'time_ago' => $news->published_at->diffForHumans(),
                    ];
                });

            return $internal->merge($aggregated)->sortByDesc('published_at')->values();
        });

        return response()->json([
            'success' => true,
            'data' => $breakingNews,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get trending topics
     */
    public function trendingTopics(Request $request)
    {
        $limit = $request->get('limit', 10);

        $trending = Cache::remember("api.trending_topics.{$limit}", 300, function () use ($limit) {
            return TrendingTopic::where('is_active', true)
                ->orderBy('engagement_score', 'desc')
                ->take($limit)
                ->get()
                ->map(function ($topic) {
                    return [
                        'topic' => $topic->topic,
                        'news_count' => $topic->news_count,
                        'engagement_score' => $topic->engagement_score,
                        'trend_velocity' => $topic->trend_velocity,
                        'related_keywords' => $topic->related_keywords,
                        'url' => route('news.topic', $topic->topic),
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'data' => $trending,
        ]);
    }

    /**
     * Get trending news
     */
    public function trendingNews(Request $request)
    {
        $limit = $request->get('limit', 20);
        $category = $request->get('category');

        $query = AggregatedNews::with(['source', 'category'])
            ->where('is_trending', true)
            ->where('status', 'published');

        if ($category) {
            $query->where('category_id', $category);
        }

        $trendingNews = $query->orderBy('engagement_score', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($news) {
                return [
                    'id' => $news->id,
                    'title' => $news->title,
                    'excerpt' => \Str::limit(strip_tags($news->content), 150),
                    'url' => $news->original_url,
                    'image' => $news->image,
                    'source' => [
                        'name' => $news->source->name ?? null,
                        'logo' => $news->source->logo_url ?? null,
                    ],
                    'category' => $news->category->name ?? null,
                    'engagement_score' => $news->engagement_score,
                    'sentiment_score' => $news->sentiment_score,
                    'published_at' => $news->published_at->toIso8601String(),
                    'time_ago' => $news->published_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $trendingNews,
        ]);
    }

    /**
     * Get latest aggregated news
     */
    public function latestNews(Request $request)
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $source = $request->get('source');
        $category = $request->get('category');

        $query = AggregatedNews::with(['source', 'category'])
            ->where('status', 'published');

        if ($source) {
            $query->where('news_source_id', $source);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        $news = $query->orderBy('published_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $news->items(),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ],
        ]);
    }

    /**
     * Get news sources
     */
    public function sources()
    {
        $sources = Cache::remember('api.news_sources', 3600, function () {
            return NewsSource::where('is_active', true)
                ->orderBy('priority', 'desc')
                ->get()
                ->map(function ($source) {
                    return [
                        'id' => $source->id,
                        'name' => $source->name,
                        'slug' => $source->slug,
                        'logo' => $source->logo_url,
                        'website' => $source->website_url,
                        'credibility_score' => $source->credibility_score,
                        'language' => $source->language,
                        'region' => $source->region,
                        'news_count' => $source->news_count,
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }

    /**
     * Search news
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 20);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Query must be at least 2 characters',
            ], 400);
        }

        // Search internal news
        $internal = News::with(['category'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->activeEntries()
            ->take($limit / 2)
            ->get()
            ->map(function ($news) {
                return [
                    'id' => $news->id,
                    'type' => 'internal',
                    'title' => $news->title,
                    'excerpt' => \Str::limit(strip_tags($news->content), 150),
                    'url' => route('news-details', $news->slug),
                    'image' => asset($news->image),
                    'category' => $news->category->name ?? null,
                    'published_at' => $news->created_at->toIso8601String(),
                ];
            });

        // Search aggregated news
        $aggregated = AggregatedNews::with(['source', 'category'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->where('status', 'published')
            ->take($limit / 2)
            ->get()
            ->map(function ($news) {
                return [
                    'id' => $news->id,
                    'type' => 'aggregated',
                    'title' => $news->title,
                    'excerpt' => \Str::limit(strip_tags($news->content), 150),
                    'url' => $news->original_url,
                    'image' => $news->image,
                    'source' => $news->source->name ?? null,
                    'category' => $news->category->name ?? null,
                    'published_at' => $news->published_at->toIso8601String(),
                ];
            });

        $results = $internal->merge($aggregated);

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => $results,
            'total' => $results->count(),
        ]);
    }

    /**
     * Get real-time stats (for admin dashboard)
     */
    public function realtimeStats()
    {
        $stats = [
            'active_users' => rand(50, 500), // Simulated - replace with real tracking
            'views_last_hour' => Analytics::where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subHour())
                ->count(),
            'views_last_5min' => Analytics::where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->count(),
            'currently_reading' => rand(20, 100), // Simulated
            'top_article_now' => AggregatedNews::where('status', 'published')
                ->orderBy('views_count', 'desc')
                ->first(['id', 'title']),
        ];

        return response()->json($stats);
    }

    /**
     * Track analytics event
     */
    public function trackEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string|in:page_view,news_view,click,share,comment',
            'trackable_type' => 'nullable|string',
            'trackable_id' => 'nullable|integer',
        ]);

        try {
            Analytics::create([
                'event_type' => $request->event_type,
                'trackable_type' => $request->trackable_type,
                'trackable_id' => $request->trackable_id,
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'country' => $request->header('CF-IPCountry') ?? geoip($request->ip())->country ?? null,
                'city' => geoip($request->ip())->city ?? null,
                'device_type' => $this->detectDevice($request->userAgent()),
                'browser' => $this->detectBrowser($request->userAgent()),
                'referrer' => $request->header('referer'),
                'page_url' => $request->get('page_url'),
                'metadata' => $request->get('metadata', []),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDevice($userAgent)
    {
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }
        if (preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|iemobile/i', $userAgent)) {
            return 'mobile';
        }
        return 'desktop';
    }

    /**
     * Detect browser from user agent
     */
    protected function detectBrowser($userAgent)
    {
        $browsers = [
            'Chrome' => '/chrome/i',
            'Firefox' => '/firefox/i',
            'Safari' => '/safari/i',
            'Edge' => '/edge/i',
            'Opera' => '/opera|opr/i',
            'IE' => '/msie|trident/i',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }

        return 'Other';
    }
}
