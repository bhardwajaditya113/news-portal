<?php

namespace App\Services;

use App\Models\Analytics;
use App\Models\News;
use App\Models\AggregatedNews;
use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics(string $period = 'today'): array
    {
        $cacheKey = "dashboard_analytics_{$period}";
        
        return Cache::remember($cacheKey, 300, function () use ($period) {
            $dateRange = $this->getDateRange($period);
            
            return [
                'overview' => $this->getOverviewStats($dateRange),
                'traffic' => $this->getTrafficStats($dateRange),
                'content' => $this->getContentStats($dateRange),
                'engagement' => $this->getEngagementStats($dateRange),
                'demographics' => $this->getDemographicsStats($dateRange),
                'sources' => $this->getSourceStats($dateRange),
                'trending' => $this->getTrendingStats(),
                'realtime' => $this->getRealtimeStats(),
            ];
        });
    }

    /**
     * Get date range based on period
     */
    protected function getDateRange(string $period): array
    {
        switch ($period) {
            case 'today':
                return [now()->startOfDay(), now()];
            case 'yesterday':
                return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
            case 'week':
                return [now()->startOfWeek(), now()];
            case 'month':
                return [now()->startOfMonth(), now()];
            case 'quarter':
                return [now()->startOfQuarter(), now()];
            case 'year':
                return [now()->startOfYear(), now()];
            default:
                return [now()->startOfDay(), now()];
        }
    }

    /**
     * Overview statistics
     */
    protected function getOverviewStats(array $dateRange): array
    {
        $analyticsQuery = Analytics::whereBetween('created_at', $dateRange);

        // Get previous period for comparison
        $periodLength = $dateRange[1]->diffInDays($dateRange[0]) ?: 1;
        $previousRange = [
            $dateRange[0]->copy()->subDays($periodLength),
            $dateRange[0]->copy()->subSecond()
        ];
        $previousQuery = Analytics::whereBetween('created_at', $previousRange);

        $totalViews = $analyticsQuery->clone()->where('type', 'page_view')->count();
        $previousViews = $previousQuery->clone()->where('type', 'page_view')->count();
        
        $uniqueVisitors = $analyticsQuery->clone()->distinct('session_id')->count('session_id');
        $previousVisitors = $previousQuery->clone()->distinct('session_id')->count('session_id');

        $newsViews = $analyticsQuery->clone()->where('type', 'news_view')->count();
        $previousNewsViews = $previousQuery->clone()->where('type', 'news_view')->count();

        $avgSessionDuration = $analyticsQuery->clone()->avg('time_spent') ?? 0;
        $previousAvgDuration = $previousQuery->clone()->avg('time_spent') ?? 0;

        return [
            'total_views' => [
                'value' => $totalViews,
                'change' => $this->calculateChange($totalViews, $previousViews),
                'trend' => $totalViews >= $previousViews ? 'up' : 'down'
            ],
            'unique_visitors' => [
                'value' => $uniqueVisitors,
                'change' => $this->calculateChange($uniqueVisitors, $previousVisitors),
                'trend' => $uniqueVisitors >= $previousVisitors ? 'up' : 'down'
            ],
            'news_views' => [
                'value' => $newsViews,
                'change' => $this->calculateChange($newsViews, $previousNewsViews),
                'trend' => $newsViews >= $previousNewsViews ? 'up' : 'down'
            ],
            'avg_session_duration' => [
                'value' => round($avgSessionDuration, 2),
                'formatted' => $this->formatDuration($avgSessionDuration),
                'change' => $this->calculateChange($avgSessionDuration, $previousAvgDuration),
                'trend' => $avgSessionDuration >= $previousAvgDuration ? 'up' : 'down'
            ],
            'bounce_rate' => [
                'value' => $this->calculateBounceRate($analyticsQuery->clone()),
                'trend' => 'stable'
            ]
        ];
    }

    /**
     * Traffic statistics with charts data
     */
    protected function getTrafficStats(array $dateRange): array
    {
        // Hourly traffic for the period
        $hourlyData = Analytics::whereBetween('created_at', $dateRange)
            ->where('type', 'page_view')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as visitors')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Daily traffic
        $dailyData = Analytics::whereBetween('created_at', $dateRange)
            ->where('type', 'page_view')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as visitors')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Traffic by device
        $deviceData = Analytics::whereBetween('created_at', $dateRange)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type');

        // Traffic by browser
        $browserData = Analytics::whereBetween('created_at', $dateRange)
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Traffic sources/referrers
        $referrerData = Analytics::whereBetween('created_at', $dateRange)
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'hourly' => $hourlyData,
            'daily' => $dailyData,
            'by_device' => [
                'desktop' => $deviceData['desktop'] ?? 0,
                'mobile' => $deviceData['mobile'] ?? 0,
                'tablet' => $deviceData['tablet'] ?? 0,
            ],
            'by_browser' => $browserData,
            'by_referrer' => $referrerData,
            'chart_labels' => $dailyData->pluck('date'),
            'chart_views' => $dailyData->pluck('views'),
            'chart_visitors' => $dailyData->pluck('visitors'),
        ];
    }

    /**
     * Content performance statistics
     */
    protected function getContentStats(array $dateRange): array
    {
        // Top performing news articles
        $topNews = News::withCount(['comments'])
            ->select('news.*')
            ->leftJoin('analytics', function ($join) use ($dateRange) {
                $join->on('news.id', '=', 'analytics.trackable_id')
                    ->where('analytics.trackable_type', News::class)
                    ->whereBetween('analytics.created_at', $dateRange);
            })
            ->selectRaw('COUNT(analytics.id) as views_count')
            ->groupBy('news.id')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        // Top performing aggregated news
        $topAggregated = AggregatedNews::with('source', 'category')
            ->whereBetween('created_at', $dateRange)
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        // Category performance
        $categoryStats = Category::select('categories.*')
            ->leftJoin('news', 'categories.id', '=', 'news.category_id')
            ->leftJoin('analytics', function ($join) use ($dateRange) {
                $join->on('news.id', '=', 'analytics.trackable_id')
                    ->where('analytics.trackable_type', News::class)
                    ->whereBetween('analytics.created_at', $dateRange);
            })
            ->selectRaw('COUNT(DISTINCT news.id) as news_count')
            ->selectRaw('COUNT(analytics.id) as views')
            ->groupBy('categories.id')
            ->orderByDesc('views')
            ->get();

        // Content published stats
        $publishedContent = [
            'internal' => News::whereBetween('created_at', $dateRange)->count(),
            'aggregated' => AggregatedNews::whereBetween('created_at', $dateRange)->count(),
        ];

        return [
            'top_news' => $topNews,
            'top_aggregated' => $topAggregated,
            'category_performance' => $categoryStats,
            'published_content' => $publishedContent,
            'total_articles' => News::count() + AggregatedNews::count(),
        ];
    }

    /**
     * Engagement statistics
     */
    protected function getEngagementStats(array $dateRange): array
    {
        $engagementQuery = Analytics::whereBetween('created_at', $dateRange);

        // Share statistics
        $shares = $engagementQuery->clone()
            ->where('type', 'share')
            ->select(
                DB::raw('JSON_EXTRACT(metadata, "$.platform") as platform'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('platform')
            ->get();

        // Comment statistics
        $comments = DB::table('comments')
            ->whereBetween('created_at', $dateRange)
            ->count();

        // Average read time
        $avgReadTime = $engagementQuery->clone()
            ->where('type', 'news_view')
            ->avg('time_spent');

        // Average scroll depth
        $avgScrollDepth = $engagementQuery->clone()
            ->whereNotNull('scroll_depth')
            ->avg('scroll_depth');

        // Newsletter subscriptions
        $newSubscribers = DB::table('subscribers')
            ->whereBetween('created_at', $dateRange)
            ->count();

        return [
            'shares' => $shares,
            'total_shares' => $shares->sum('count'),
            'comments' => $comments,
            'avg_read_time' => round($avgReadTime ?? 0, 2),
            'avg_scroll_depth' => round($avgScrollDepth ?? 0, 1),
            'new_subscribers' => $newSubscribers,
        ];
    }

    /**
     * Demographics statistics
     */
    protected function getDemographicsStats(array $dateRange): array
    {
        $analyticsQuery = Analytics::whereBetween('created_at', $dateRange);

        // By country
        $byCountry = $analyticsQuery->clone()
            ->whereNotNull('country')
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        // By city (top 10)
        $byCity = $analyticsQuery->clone()
            ->whereNotNull('city')
            ->select('city', 'country', DB::raw('COUNT(*) as count'))
            ->groupBy('city', 'country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // By region
        $byRegion = $analyticsQuery->clone()
            ->whereNotNull('region')
            ->select('region', 'country', DB::raw('COUNT(*) as count'))
            ->groupBy('region', 'country')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        // Operating systems
        $byOS = $analyticsQuery->clone()
            ->whereNotNull('os')
            ->select('os', DB::raw('COUNT(*) as count'))
            ->groupBy('os')
            ->orderByDesc('count')
            ->get();

        // World map data
        $worldMapData = $byCountry->mapWithKeys(function ($item) {
            return [$item->country => $item->count];
        });

        return [
            'by_country' => $byCountry,
            'by_city' => $byCity,
            'by_region' => $byRegion,
            'by_os' => $byOS,
            'world_map_data' => $worldMapData,
            'top_country' => $byCountry->first()?->country ?? 'N/A',
            'countries_count' => $byCountry->count(),
        ];
    }

    /**
     * News source statistics
     */
    protected function getSourceStats(array $dateRange): array
    {
        $sources = NewsSource::withCount([
            'aggregatedNews as total_news',
            'aggregatedNews as recent_news' => function ($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            }
        ])
        ->get()
        ->map(function ($source) {
            return [
                'id' => $source->id,
                'name' => $source->name,
                'logo' => $source->logo,
                'total_news' => $source->total_news,
                'recent_news' => $source->recent_news,
                'credibility_score' => $source->credibility_score,
                'last_fetched' => $source->last_fetched_at?->diffForHumans(),
                'is_active' => $source->is_active,
            ];
        });

        return [
            'sources' => $sources,
            'total_sources' => $sources->count(),
            'active_sources' => $sources->where('is_active', true)->count(),
            'total_aggregated' => $sources->sum('total_news'),
        ];
    }

    /**
     * Trending statistics
     */
    protected function getTrendingStats(): array
    {
        return [
            'trending_topics' => DB::table('trending_topics')
                ->where('is_active', true)
                ->orderByDesc('engagement_score')
                ->limit(15)
                ->get(),
            'trending_categories' => Category::withCount([
                'news' => function ($query) {
                    $query->where('created_at', '>=', now()->subDay());
                }
            ])
            ->orderByDesc('news_count')
            ->limit(10)
            ->get(),
            'breaking_news' => AggregatedNews::breaking()
                ->with('source')
                ->recent(6)
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Real-time statistics
     */
    protected function getRealtimeStats(): array
    {
        $now = now();
        
        return [
            'active_users' => Analytics::where('created_at', '>=', $now->subMinutes(5))
                ->distinct('session_id')
                ->count('session_id'),
            'views_last_hour' => Analytics::where('created_at', '>=', $now->subHour())
                ->where('type', 'page_view')
                ->count(),
            'views_last_5min' => Analytics::where('created_at', '>=', $now->subMinutes(5))
                ->where('type', 'page_view')
                ->count(),
            'currently_reading' => Analytics::where('created_at', '>=', $now->subMinutes(2))
                ->where('type', 'news_view')
                ->select('trackable_id')
                ->distinct()
                ->get()
                ->count(),
            'timestamp' => $now->toIso8601String(),
        ];
    }

    /**
     * Calculate percentage change
     */
    protected function calculateChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Calculate bounce rate
     */
    protected function calculateBounceRate($query): float
    {
        $totalSessions = $query->clone()->distinct('session_id')->count('session_id');
        if ($totalSessions == 0) return 0;

        $bouncedSessions = DB::table('analytics')
            ->select('session_id')
            ->groupBy('session_id')
            ->havingRaw('COUNT(*) = 1')
            ->get()
            ->count();

        return round(($bouncedSessions / $totalSessions) * 100, 2);
    }

    /**
     * Format duration in seconds to human readable
     */
    protected function formatDuration($seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . 'm';
        }
        return round($seconds / 3600, 1) . 'h';
    }

    /**
     * Track page view
     */
    public function trackPageView(array $data): Analytics
    {
        return Analytics::create(array_merge($data, ['type' => 'page_view']));
    }

    /**
     * Track news view
     */
    public function trackNewsView($news, array $data): Analytics
    {
        return Analytics::create(array_merge($data, [
            'type' => 'news_view',
            'trackable_type' => get_class($news),
            'trackable_id' => $news->id,
        ]));
    }

    /**
     * Get chart data for specific metric
     */
    public function getChartData(string $metric, string $period = 'week', string $granularity = 'daily'): array
    {
        $dateRange = $this->getDateRange($period);
        
        $dateFormat = match ($granularity) {
            'hourly' => '%Y-%m-%d %H:00',
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%W',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $data = Analytics::whereBetween('created_at', $dateRange)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as label"),
                DB::raw('COUNT(*) as value')
            )
            ->when($metric === 'visitors', function ($query) {
                $query->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as label"),
                    DB::raw('COUNT(DISTINCT session_id) as value')
                );
            })
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels' => $data->pluck('label'),
            'data' => $data->pluck('value'),
        ];
    }
}
