<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\NewsAggregatorService;
use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use App\Models\AggregatedNews;
use App\Models\NewsSource;
use App\Models\TrendingTopic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdvancedDashboardController extends Controller
{
    protected $analyticsService;
    protected $aggregatorService;

    public function __construct(AnalyticsService $analyticsService, NewsAggregatorService $aggregatorService)
    {
        $this->analyticsService = $analyticsService;
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Main dashboard view
     */
    public function index(Request $request): View
    {
        $period = $request->get('period', 'today');
        $analytics = $this->analyticsService->getDashboardAnalytics($period);

        // Additional quick stats
        $quickStats = [
            'total_internal_news' => News::count(),
            'total_aggregated_news' => AggregatedNews::count(),
            'total_categories' => Category::count(),
            'total_sources' => NewsSource::count(),
            'active_sources' => NewsSource::active()->count(),
            'pending_news' => News::where('is_approved', 0)->count(),
            'breaking_news' => AggregatedNews::breaking()->count(),
            'trending_topics' => TrendingTopic::active()->count(),
        ];

        return view('admin.dashboard.advanced', compact('analytics', 'quickStats', 'period'));
    }

    /**
     * Real-time analytics endpoint for live updates
     */
    public function realtime(): JsonResponse
    {
        return response()->json($this->analyticsService->getRealtimeStats());
    }

    /**
     * Get chart data for specific metrics
     */
    public function chartData(Request $request): JsonResponse
    {
        $metric = $request->get('metric', 'views');
        $period = $request->get('period', 'week');
        $granularity = $request->get('granularity', 'daily');

        $data = $this->analyticsService->getChartData($metric, $period, $granularity);

        return response()->json($data);
    }

    /**
     * Demographics dashboard
     */
    public function demographics(Request $request): View
    {
        $period = $request->get('period', 'month');
        $analytics = $this->analyticsService->getDashboardAnalytics($period);

        return view('admin.dashboard.demographics', [
            'demographics' => $analytics['demographics'],
            'period' => $period
        ]);
    }

    /**
     * Content performance dashboard
     */
    public function content(Request $request): View
    {
        $period = $request->get('period', 'week');
        $analytics = $this->analyticsService->getDashboardAnalytics($period);

        return view('admin.dashboard.content', [
            'content' => $analytics['content'],
            'period' => $period
        ]);
    }

    /**
     * News sources management
     */
    public function sources(): View
    {
        $sources = NewsSource::withCount('aggregatedNews')
            ->orderBy('priority', 'desc')
            ->get();

        return view('admin.dashboard.sources', compact('sources'));
    }

    /**
     * Trending topics view
     */
    public function trending(): View
    {
        $topics = TrendingTopic::active()
            ->orderBy('engagement_score', 'desc')
            ->paginate(50);

        $breakingNews = AggregatedNews::breaking()
            ->with('source', 'category')
            ->recent(12)
            ->get();

        return view('admin.dashboard.trending', compact('topics', 'breakingNews'));
    }

    /**
     * Export analytics data
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'csv');
        $analytics = $this->analyticsService->getDashboardAnalytics($period);

        $filename = "analytics_report_{$period}_" . now()->format('Y-m-d') . ".{$format}";

        return response()->streamDownload(function () use ($analytics) {
            $handle = fopen('php://output', 'w');
            
            // Headers
            fputcsv($handle, ['Metric', 'Value', 'Change', 'Trend']);
            
            // Overview stats
            foreach ($analytics['overview'] as $metric => $data) {
                fputcsv($handle, [
                    ucwords(str_replace('_', ' ', $metric)),
                    $data['value'],
                    $data['change'] . '%',
                    $data['trend']
                ]);
            }
            
            fclose($handle);
        }, $filename);
    }
}
