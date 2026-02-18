<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AggregatedNews;
use App\Models\Category;
use App\Models\News;
use App\Models\NewsSource;
use App\Models\TrendingTopic;
use App\Models\UserPreference;
use App\Services\NewsAggregatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EnhancedHomeController extends Controller
{
    protected $aggregatorService;

    public function __construct(NewsAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Enhanced home page with world-class features
     */
    public function index()
    {
        // Breaking news for ticker
        $breakingNews = $this->getBreakingNews();

        // Trending topics
        $trendingTopics = $this->getTrendingTopics();

        // Personalized news for logged-in users
        $personalizedNews = $this->getPersonalizedNews();

        // Category-wise news
        $categoryNews = $this->getCategoryNews();

        // Aggregated world news
        $aggregatedNews = $this->getAggregatedNews();

        // News sources for filters
        $newsSources = NewsSource::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->take(10)
            ->get();

        // Featured stories
        $featuredStories = News::with(['category', 'auther'])
            ->where('show_at_slider', 1)
            ->activeEntries()
            ->withLocalize()
            ->orderBy('id', 'DESC')
            ->take(5)
            ->get();

        // Most viewed (for sidebar)
        $mostViewed = News::with(['category'])
            ->activeEntries()
            ->withLocalize()
            ->orderBy('views', 'DESC')
            ->take(5)
            ->get();

        // Recent news
        $recentNews = News::with(['category', 'auther'])
            ->activeEntries()
            ->withLocalize()
            ->orderBy('id', 'DESC')
            ->take(10)
            ->get();

        return view('frontend.home-enhanced', compact(
            'breakingNews',
            'trendingTopics',
            'personalizedNews',
            'categoryNews',
            'aggregatedNews',
            'newsSources',
            'featuredStories',
            'mostViewed',
            'recentNews'
        ));
    }

    /**
     * Get breaking news for ticker
     */
    protected function getBreakingNews()
    {
        return Cache::remember('frontend.breaking_news', 60, function () {
            // Internal breaking news
            $internalBreaking = News::where('is_breaking_news', 1)
                ->activeEntries()
                ->withLocalize()
                ->orderBy('id', 'DESC')
                ->take(5)
                ->get();

            // Aggregated breaking news
            $aggregatedBreaking = AggregatedNews::with('source')
                ->where('is_breaking', true)
                ->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->take(5)
                ->get();

            return $internalBreaking->merge($aggregatedBreaking);
        });
    }

    /**
     * Get trending topics
     */
    protected function getTrendingTopics()
    {
        return Cache::remember('frontend.trending_topics', 300, function () {
            return TrendingTopic::where('is_active', true)
                ->orderBy('engagement_score', 'desc')
                ->take(10)
                ->get();
        });
    }

    /**
     * Get personalized news for user
     */
    protected function getPersonalizedNews()
    {
        if (!Auth::check()) {
            // Return popular news for guests
            return News::with(['category', 'auther'])
                ->activeEntries()
                ->withLocalize()
                ->orderBy('views', 'DESC')
                ->take(5)
                ->get();
        }

        $userId = Auth::id();

        return Cache::remember("frontend.personalized.{$userId}", 300, function () use ($userId) {
            $preference = UserPreference::where('user_id', $userId)->first();

            if (!$preference) {
                return News::with(['category', 'auther'])
                    ->activeEntries()
                    ->withLocalize()
                    ->orderBy('id', 'DESC')
                    ->take(5)
                    ->get();
            }

            $preferredCategories = $preference->preferred_categories ?? [];

            return News::with(['category', 'auther'])
                ->when(!empty($preferredCategories), function ($query) use ($preferredCategories) {
                    $query->whereIn('category_id', $preferredCategories);
                })
                ->activeEntries()
                ->withLocalize()
                ->orderBy('id', 'DESC')
                ->take(5)
                ->get();
        });
    }

    /**
     * Get category-wise news
     */
    protected function getCategoryNews()
    {
        return Cache::remember('frontend.category_news.' . getLangauge(), 600, function () {
            return Category::with(['news' => function ($query) {
                $query->activeEntries()
                    ->withLocalize()
                    ->orderBy('id', 'DESC')
                    ->take(5);
            }])
                ->where('status', 1)
                ->where('language', getLangauge())
                ->take(6)
                ->get();
        });
    }

    /**
     * Get aggregated world news
     */
    protected function getAggregatedNews()
    {
        return Cache::remember('frontend.aggregated_news', 300, function () {
            return AggregatedNews::with(['source', 'category'])
                ->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->take(9)
                ->get();
        });
    }

    /**
     * API endpoint for real-time breaking news
     */
    public function apiBreakingNews()
    {
        $breakingNews = $this->getBreakingNews();

        return response()->json($breakingNews->map(function ($news) {
            return [
                'id' => $news->id,
                'title' => $news->title,
                'url' => $news->original_url ?? route('news.show', $news->slug ?? $news->id),
                'source' => $news->source->name ?? config('app.name'),
                'time' => $news->published_at ?? $news->created_at,
            ];
        }));
    }

    /**
     * Get trending news page
     */
    public function trending()
    {
        $trendingTopics = TrendingTopic::where('is_active', true)
            ->orderBy('engagement_score', 'desc')
            ->paginate(20);

        $trendingNews = AggregatedNews::with(['source', 'category'])
            ->where('is_trending', true)
            ->where('status', 'published')
            ->orderBy('engagement_score', 'desc')
            ->paginate(20);

        return view('frontend.trending', compact('trendingTopics', 'trendingNews'));
    }

    /**
     * Search news across internal and aggregated
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return redirect()->back();
        }

        // Search internal news
        $internalResults = News::with(['category', 'auther'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->activeEntries()
            ->withLocalize()
            ->take(20)
            ->get();

        // Search aggregated news
        $aggregatedResults = AggregatedNews::with(['source', 'category'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->where('status', 'published')
            ->take(20)
            ->get();

        $results = $internalResults->merge($aggregatedResults);

        return view('frontend.search-results', compact('results', 'query'));
    }

    /**
     * Save user preferences
     */
    public function savePreferences(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'preferred_categories' => 'array',
            'preferred_sources' => 'array',
        ]);

        UserPreference::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'preferred_categories' => $request->preferred_categories,
                'preferred_sources' => $request->preferred_sources,
            ]
        );

        // Clear cache
        Cache::forget("frontend.personalized." . Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * News by topic/tag
     */
    public function byTopic($topic)
    {
        $news = News::with(['category', 'auther'])
            ->whereHas('tags', function ($query) use ($topic) {
                $query->where('name', $topic);
            })
            ->activeEntries()
            ->withLocalize()
            ->paginate(20);

        $aggregatedNews = AggregatedNews::with(['source', 'category'])
            ->where(function ($query) use ($topic) {
                $query->whereJsonContains('keywords', $topic)
                    ->orWhere('title', 'like', "%{$topic}%");
            })
            ->orderBy('published_at', 'DESC')
            ->paginate(20);

        $topicInfo = TrendingTopic::where('topic', $topic)->first();
        
        // Get related topics
        $relatedTopics = TrendingTopic::where('topic', '!=', $topic)
            ->where('is_active', true)
            ->orderBy('engagement_score', 'DESC')
            ->take(10)
            ->get();

        return view('frontend.topic', compact('news', 'aggregatedNews', 'topic', 'topicInfo', 'relatedTopics'));
    }

    /**
     * News by source
     */
    public function bySource($sourceSlug)
    {
        $source = NewsSource::where('slug', $sourceSlug)->firstOrFail();

        $news = AggregatedNews::with(['category'])
            ->where('news_source_id', $source->id)
            ->orderBy('published_at', 'desc')
            ->paginate(20);
        
        // Get other sources for sidebar
        $otherSources = NewsSource::where('slug', '!=', $sourceSlug)
            ->where('is_active', true)
            ->orderBy('priority', 'DESC')
            ->take(6)
            ->get();

        return view('frontend.source', compact('source', 'news', 'otherSources'));
    }
}
