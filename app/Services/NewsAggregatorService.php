<?php

namespace App\Services;

use App\Models\NewsSource;
use App\Models\AggregatedNews;
use App\Models\Category;
use App\Models\TrendingTopic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Carbon\Carbon;

class NewsAggregatorService
{
    protected $sources = [];
    
    /**
     * Pre-configured major news sources with their RSS feeds
     */
    public static function getDefaultSources(): array
    {
        return [
            [
                'name' => 'BBC News',
                'slug' => 'bbc-news',
                'website_url' => 'https://www.bbc.com/news',
                'rss_feed_url' => 'https://feeds.bbci.co.uk/news/rss.xml',
                'api_type' => 'rss',
                'country' => 'GB',
                'language' => 'en',
                'credibility_score' => 0.95,
                'priority' => 95,
                'category_mapping' => [
                    'World' => 'world',
                    'Business' => 'business',
                    'Politics' => 'politics',
                    'Tech' => 'technology',
                    'Science' => 'science',
                    'Health' => 'health',
                    'Entertainment' => 'entertainment',
                    'Sports' => 'sports'
                ]
            ],
            [
                'name' => 'Reuters',
                'slug' => 'reuters',
                'website_url' => 'https://www.reuters.com',
                'rss_feed_url' => 'https://www.reutersagency.com/feed/',
                'api_type' => 'rss',
                'country' => 'US',
                'language' => 'en',
                'credibility_score' => 0.98,
                'priority' => 98,
                'category_mapping' => [
                    'World' => 'world',
                    'Business' => 'business',
                    'Markets' => 'business',
                    'Technology' => 'technology'
                ]
            ],
            [
                'name' => 'CNN',
                'slug' => 'cnn',
                'website_url' => 'https://edition.cnn.com',
                'rss_feed_url' => 'http://rss.cnn.com/rss/edition.rss',
                'api_type' => 'rss',
                'country' => 'US',
                'language' => 'en',
                'credibility_score' => 0.88,
                'priority' => 88,
                'category_mapping' => [
                    'World' => 'world',
                    'US' => 'politics',
                    'Business' => 'business',
                    'Tech' => 'technology',
                    'Entertainment' => 'entertainment',
                    'Sport' => 'sports',
                    'Travel' => 'lifestyle'
                ]
            ],
            [
                'name' => 'Al Jazeera',
                'slug' => 'al-jazeera',
                'website_url' => 'https://www.aljazeera.com',
                'rss_feed_url' => 'https://www.aljazeera.com/xml/rss/all.xml',
                'api_type' => 'rss',
                'country' => 'QA',
                'language' => 'en',
                'credibility_score' => 0.85,
                'priority' => 85,
                'category_mapping' => [
                    'News' => 'world',
                    'Economy' => 'business',
                    'Opinion' => 'opinion'
                ]
            ],
            [
                'name' => 'The Hindu',
                'slug' => 'the-hindu',
                'website_url' => 'https://www.thehindu.com',
                'rss_feed_url' => 'https://www.thehindu.com/feeder/default.rss',
                'api_type' => 'rss',
                'country' => 'IN',
                'language' => 'en',
                'credibility_score' => 0.90,
                'priority' => 90,
                'category_mapping' => [
                    'National' => 'national',
                    'International' => 'world',
                    'Business' => 'business',
                    'Sport' => 'sports',
                    'Entertainment' => 'entertainment',
                    'Science' => 'science'
                ]
            ],
            [
                'name' => 'Times of India',
                'slug' => 'times-of-india',
                'website_url' => 'https://timesofindia.indiatimes.com',
                'rss_feed_url' => 'https://timesofindia.indiatimes.com/rssfeedstopstories.cms',
                'api_type' => 'rss',
                'country' => 'IN',
                'language' => 'en',
                'credibility_score' => 0.85,
                'priority' => 85,
                'category_mapping' => [
                    'India' => 'national',
                    'World' => 'world',
                    'Business' => 'business',
                    'Tech' => 'technology',
                    'Sports' => 'sports',
                    'Entertainment' => 'entertainment'
                ]
            ],
            [
                'name' => 'NDTV',
                'slug' => 'ndtv',
                'website_url' => 'https://www.ndtv.com',
                'rss_feed_url' => 'https://feeds.feedburner.com/ndtvnews-top-stories',
                'api_type' => 'rss',
                'country' => 'IN',
                'language' => 'en',
                'credibility_score' => 0.87,
                'priority' => 87,
                'category_mapping' => [
                    'India' => 'national',
                    'World' => 'world',
                    'Business' => 'business'
                ]
            ],
            [
                'name' => 'Indian Express',
                'slug' => 'indian-express',
                'website_url' => 'https://indianexpress.com',
                'rss_feed_url' => 'https://indianexpress.com/feed/',
                'api_type' => 'rss',
                'country' => 'IN',
                'language' => 'en',
                'credibility_score' => 0.88,
                'priority' => 88,
                'category_mapping' => [
                    'India' => 'national',
                    'World' => 'world',
                    'Business' => 'business',
                    'Sports' => 'sports'
                ]
            ],
            [
                'name' => 'Economic Times',
                'slug' => 'economic-times',
                'website_url' => 'https://economictimes.indiatimes.com',
                'rss_feed_url' => 'https://economictimes.indiatimes.com/rssfeedstopstories.cms',
                'api_type' => 'rss',
                'country' => 'IN',
                'language' => 'en',
                'credibility_score' => 0.90,
                'priority' => 90,
                'category_mapping' => [
                    'Markets' => 'business',
                    'Economy' => 'business',
                    'Tech' => 'technology',
                    'Industry' => 'business'
                ]
            ],
            [
                'name' => 'Deutsche Welle',
                'slug' => 'deutsche-welle',
                'website_url' => 'https://www.dw.com',
                'rss_feed_url' => 'https://rss.dw.com/rdf/rss-en-all',
                'api_type' => 'rss',
                'country' => 'DE',
                'language' => 'en',
                'credibility_score' => 0.92,
                'priority' => 92,
                'category_mapping' => [
                    'World' => 'world',
                    'Europe' => 'world',
                    'Business' => 'business',
                    'Science' => 'science',
                    'Culture' => 'entertainment'
                ]
            ],
            [
                'name' => 'AP News',
                'slug' => 'ap-news',
                'website_url' => 'https://apnews.com',
                'rss_feed_url' => 'https://rsshub.app/apnews/topics/apf-topnews',
                'api_type' => 'rss',
                'country' => 'US',
                'language' => 'en',
                'credibility_score' => 0.97,
                'priority' => 97,
                'category_mapping' => [
                    'Top News' => 'world',
                    'Politics' => 'politics',
                    'Business' => 'business',
                    'Technology' => 'technology',
                    'Science' => 'science',
                    'Entertainment' => 'entertainment',
                    'Sports' => 'sports'
                ]
            ],
            [
                'name' => 'Google News',
                'slug' => 'google-news',
                'website_url' => 'https://news.google.com',
                'rss_feed_url' => 'https://news.google.com/rss?hl=en-IN&gl=IN&ceid=IN:en',
                'api_type' => 'rss',
                'country' => 'US',
                'language' => 'en',
                'credibility_score' => 0.85,
                'priority' => 80,
                'category_mapping' => [
                    'Top Stories' => 'world'
                ]
            ]
        ];
    }

    /**
     * Fetch news from all active sources
     */
    public function fetchAllNews(): array
    {
        $results = [];
        $sources = NewsSource::active()->byPriority()->get();

        foreach ($sources as $source) {
            try {
                $news = $this->fetchFromSource($source);
                $results[$source->slug] = [
                    'success' => true,
                    'count' => count($news),
                    'items' => $news
                ];

                $source->update(['last_fetched_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Failed to fetch from {$source->name}: " . $e->getMessage());
                $results[$source->slug] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Update trending topics after fetching
        $this->updateTrendingTopics();

        return $results;
    }

    /**
     * Fetch news from a specific source
     */
    public function fetchFromSource($source): array
    {
        // Accept both ID and model
        if (is_numeric($source)) {
            $source = NewsSource::findOrFail($source);
        }
        
        switch ($source->api_type) {
            case 'rss':
                return $this->fetchRssFeed($source);
            case 'rest':
                return $this->fetchRestApi($source);
            default:
                return [];
        }
    }

    /**
     * Parse and store RSS feed
     */
    protected function fetchRssFeed(NewsSource $source): array
    {
        $response = Http::timeout(30)->get($source->rss_feed_url);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch RSS feed: HTTP " . $response->status());
        }

        $xml = new SimpleXMLElement($response->body());
        $items = [];

        foreach ($xml->channel->item as $item) {
            $newsItem = $this->parseRssItem($item, $source);
            if ($newsItem) {
                $items[] = $newsItem;
            }
        }

        return $items;
    }

    /**
     * Parse individual RSS item
     */
    protected function parseRssItem($item, NewsSource $source): ?array
    {
        $title = (string) $item->title;
        $link = (string) $item->link;
        
        // Skip if already exists
        $externalId = md5($link);
        if (AggregatedNews::where('news_source_id', $source->id)->where('external_id', $externalId)->exists()) {
            return null;
        }

        // Extract image
        $image = null;
        if (isset($item->enclosure['url'])) {
            $image = (string) $item->enclosure['url'];
        } elseif (isset($item->children('media', true)->content)) {
            $image = (string) $item->children('media', true)->content->attributes()['url'];
        }

        // Parse publish date
        $pubDate = isset($item->pubDate) ? Carbon::parse((string) $item->pubDate) : now();

        // Determine category
        $category = $this->mapCategory((string) ($item->category ?? 'General'), $source);

        // Extract keywords from title
        $keywords = $this->extractKeywords($title);

        $data = [
            'news_source_id' => $source->id,
            'external_id' => $externalId,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(6),
            'content' => strip_tags((string) ($item->children('content', true)->encoded ?? $item->description)),
            'summary' => Str::limit(strip_tags((string) $item->description), 300),
            'image' => $image,
            'original_url' => $link,
            'author' => (string) ($item->children('dc', true)->creator ?? $source->name),
            'category_id' => $category?->id,
            'published_at' => $pubDate,
            'language' => $source->language,
            'country' => $source->country,
            'keywords' => $keywords,
            'is_breaking' => $this->isBreakingNews($title),
        ];

        $news = AggregatedNews::create($data);
        return $news->toArray();
    }

    /**
     * Map external category to internal category
     */
    protected function mapCategory(string $externalCategory, NewsSource $source): ?Category
    {
        $mapping = $source->category_mapping ?? [];
        $internalSlug = $mapping[$externalCategory] ?? Str::slug($externalCategory);
        
        return Category::where('slug', $internalSlug)
            ->orWhere('name', 'like', "%{$externalCategory}%")
            ->first();
    }

    /**
     * Extract keywords from text
     */
    protected function extractKeywords(string $text): array
    {
        // Remove common words and extract meaningful keywords
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need', 'dare', 'ought', 'used', 'that', 'which', 'who', 'whom', 'this', 'these', 'those', 'it', 'its'];
        
        $words = preg_split('/\s+/', strtolower($text));
        $keywords = [];
        
        foreach ($words as $word) {
            $word = preg_replace('/[^a-z0-9]/', '', $word);
            if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        return array_slice(array_unique($keywords), 0, 10);
    }

    /**
     * Check if news is breaking based on keywords
     */
    protected function isBreakingNews(string $title): bool
    {
        $breakingKeywords = ['breaking', 'urgent', 'alert', 'just in', 'developing', 'flash', 'live'];
        $titleLower = strtolower($title);
        
        foreach ($breakingKeywords as $keyword) {
            if (strpos($titleLower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Update trending topics based on news data
     */
    public function updateTrendingTopics(): void
    {
        // Get news from last 24 hours
        $recentNews = AggregatedNews::where('published_at', '>=', now()->subDay())
            ->select('title', 'keywords', 'views_count', 'engagement_score')
            ->get();

        $topicScores = [];

        foreach ($recentNews as $news) {
            $keywords = $news->keywords ?? [];
            foreach ($keywords as $keyword) {
                if (!isset($topicScores[$keyword])) {
                    $topicScores[$keyword] = [
                        'count' => 0,
                        'views' => 0,
                        'engagement' => 0
                    ];
                }
                $topicScores[$keyword]['count']++;
                $topicScores[$keyword]['views'] += $news->views_count;
                $topicScores[$keyword]['engagement'] += $news->engagement_score;
            }
        }

        // Sort by combined score
        uasort($topicScores, function ($a, $b) {
            $scoreA = ($a['count'] * 10) + ($a['views'] * 0.1) + $a['engagement'];
            $scoreB = ($b['count'] * 10) + ($b['views'] * 0.1) + $b['engagement'];
            return $scoreB <=> $scoreA;
        });

        // Take top 50 topics
        $topTopics = array_slice($topicScores, 0, 50, true);

        foreach ($topTopics as $topic => $scores) {
            TrendingTopic::updateOrCreate(
                ['topic' => $topic],
                [
                    'slug' => Str::slug($topic),
                    'news_count' => $scores['count'],
                    'views_count' => $scores['views'],
                    'engagement_score' => $scores['engagement'],
                    'is_active' => true
                ]
            );
        }
    }

    /**
     * Get real-time trending news
     */
    public function getTrendingNews(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('trending_news', 300, function () use ($limit) {
            return AggregatedNews::trending()
                ->with('source', 'category')
                ->recent(48)
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get breaking news
     */
    public function getBreakingNews(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('breaking_news', 60, function () use ($limit) {
            return AggregatedNews::breaking()
                ->with('source', 'category')
                ->recent(6)
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get personalized news feed for user
     */
    public function getPersonalizedFeed($user, int $limit = 30): \Illuminate\Database\Eloquent\Collection
    {
        $preferences = $user->preferences ?? null;
        
        $query = AggregatedNews::with('source', 'category')
            ->recent(72);

        if ($preferences) {
            if ($preferences->preferred_categories) {
                $query->whereIn('category_id', $preferences->preferred_categories);
            }
            if ($preferences->preferred_sources) {
                $query->whereIn('news_source_id', $preferences->preferred_sources);
            }
            if ($preferences->preferred_languages) {
                $query->whereIn('language', $preferences->preferred_languages);
            }
        }

        return $query->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search aggregated news
     */
    public function searchNews(string $query, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $newsQuery = AggregatedNews::with('source', 'category');

        // Full-text search
        $newsQuery->where(function ($q) use ($query) {
            $q->whereFullText(['title', 'summary'], $query)
              ->orWhere('title', 'like', "%{$query}%");
        });

        // Apply filters
        if (!empty($filters['category'])) {
            $newsQuery->where('category_id', $filters['category']);
        }
        if (!empty($filters['source'])) {
            $newsQuery->where('news_source_id', $filters['source']);
        }
        if (!empty($filters['date_from'])) {
            $newsQuery->where('published_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $newsQuery->where('published_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['country'])) {
            $newsQuery->where('country', $filters['country']);
        }

        return $newsQuery->orderBy('published_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Initialize default news sources
     */
    public function initializeDefaultSources(): int
    {
        $count = 0;
        foreach (self::getDefaultSources() as $sourceData) {
            NewsSource::updateOrCreate(
                ['slug' => $sourceData['slug']],
                $sourceData
            );
            $count++;
        }
        return $count;
    }
}
