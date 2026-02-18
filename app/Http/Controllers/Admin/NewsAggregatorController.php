<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsSource;
use App\Models\AggregatedNews;
use App\Services\NewsAggregatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class NewsAggregatorController extends Controller
{
    protected $aggregatorService;

    public function __construct(NewsAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * List all news sources
     */
    public function index(): View
    {
        $sources = NewsSource::withCount('aggregatedNews')
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return view('admin.aggregator.index', compact('sources'));
    }

    /**
     * Create new news source form
     */
    public function create(): View
    {
        return view('admin.aggregator.create');
    }

    /**
     * Store new news source
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'required|url',
            'rss_feed_url' => 'nullable|url',
            'api_type' => 'required|in:rss,rest,graphql,scrape',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|string',
            'country' => 'required|string|size:2',
            'language' => 'required|string|max:5',
            'credibility_score' => 'required|numeric|min:0|max:1',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'fetch_interval' => 'required|integer|min:5',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);
        
        NewsSource::create($validated);

        toast(__('admin.News source created successfully!'), 'success');
        return redirect()->route('admin.aggregator.index');
    }

    /**
     * Edit news source form
     */
    public function edit(NewsSource $source): View
    {
        return view('admin.aggregator.edit', compact('source'));
    }

    /**
     * Update news source
     */
    public function update(Request $request, NewsSource $source)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'required|url',
            'rss_feed_url' => 'nullable|url',
            'api_type' => 'required|in:rss,rest,graphql,scrape',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|string',
            'country' => 'required|string|size:2',
            'language' => 'required|string|max:5',
            'credibility_score' => 'required|numeric|min:0|max:1',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'fetch_interval' => 'required|integer|min:5',
        ]);

        $source->update($validated);

        toast(__('admin.News source updated successfully!'), 'success');
        return redirect()->route('admin.aggregator.index');
    }

    /**
     * Delete news source
     */
    public function destroy(NewsSource $source): JsonResponse
    {
        $source->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('admin.News source deleted successfully!')
        ]);
    }

    /**
     * Fetch news from specific source
     */
    public function fetchSource(NewsSource $source): JsonResponse
    {
        try {
            $news = $this->aggregatorService->fetchFromSource($source);
            
            return response()->json([
                'status' => 'success',
                'message' => "Fetched " . count($news) . " news items from {$source->name}",
                'count' => count($news)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Failed to fetch: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch news from all sources
     */
    public function fetchAll(): JsonResponse
    {
        $results = $this->aggregatorService->fetchAllNews();
        
        $successful = collect($results)->filter(fn($r) => $r['success'])->count();
        $totalNews = collect($results)->filter(fn($r) => $r['success'])->sum('count');

        return response()->json([
            'status' => 'success',
            'message' => "Fetched {$totalNews} news items from {$successful} sources",
            'results' => $results
        ]);
    }

    /**
     * Initialize default news sources
     */
    public function initializeDefaults(): JsonResponse
    {
        $count = $this->aggregatorService->initializeDefaultSources();

        return response()->json([
            'status' => 'success',
            'message' => "Initialized {$count} default news sources"
        ]);
    }

    /**
     * Toggle source active status
     */
    public function toggleStatus(NewsSource $source): JsonResponse
    {
        $source->update(['is_active' => !$source->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully',
            'is_active' => $source->is_active
        ]);
    }

    /**
     * View aggregated news
     */
    public function aggregatedNews(Request $request): View
    {
        $query = AggregatedNews::with('source', 'category');

        if ($request->filled('source')) {
            $query->where('news_source_id', $request->source);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('is_breaking')) {
            $query->where('is_breaking', true);
        }
        if ($request->filled('is_trending')) {
            $query->where('is_trending', true);
        }

        $news = $query->orderBy('published_at', 'desc')->paginate(30);
        $sources = NewsSource::active()->get();

        return view('admin.aggregator.news', compact('news', 'sources'));
    }

    /**
     * Mark news as featured/trending/breaking
     */
    public function updateNewsStatus(Request $request, AggregatedNews $news): JsonResponse
    {
        $field = $request->get('field');
        $value = $request->get('value');

        if (in_array($field, ['is_featured', 'is_trending', 'is_breaking'])) {
            $news->update([$field => $value]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid field'
        ], 400);
    }
}
