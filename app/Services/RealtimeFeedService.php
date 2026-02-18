<?php

namespace App\Services;

use App\Models\NewsSource;
use App\Models\AggregatedNews;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RealtimeFeedService
{
    const CACHE_KEY_STATUS = 'realtime_feed_status';
    const CACHE_KEY_STATS = 'realtime_feed_stats';
    const CACHE_KEY_LATEST = 'realtime_latest_news';
    const CACHE_KEY_QUEUE = 'realtime_fetch_queue';
    
    protected $fetchInterval = 1; // seconds between fetches
    protected $sourcesPerCycle = 3; // sources to fetch per cycle
    
    /**
     * Get current feed status
     */
    public static function getStatus(): array
    {
        return Cache::get(self::CACHE_KEY_STATUS, [
            'running' => false,
            'started_at' => null,
            'last_fetch' => null,
            'sources_processed' => 0,
            'articles_fetched' => 0,
            'errors' => 0,
            'current_source' => null,
        ]);
    }
    
    /**
     * Get real-time statistics
     */
    public static function getStats(): array
    {
        $stats = Cache::get(self::CACHE_KEY_STATS, [
            'total_fetched_today' => 0,
            'fetched_last_hour' => 0,
            'fetched_last_minute' => 0,
            'sources_active' => 0,
            'sources_failed' => [],
            'fetch_rate' => 0,
            'avg_response_time' => 0,
        ]);
        
        // Add live counts
        $stats['total_articles'] = AggregatedNews::count();
        $stats['breaking_news'] = AggregatedNews::where('is_breaking', true)->count();
        $stats['sources_count'] = NewsSource::where('is_active', true)->count();
        
        return $stats;
    }
    
    /**
     * Get latest fetched news for live feed
     */
    public static function getLatestNews(int $limit = 20): array
    {
        return Cache::get(self::CACHE_KEY_LATEST, []);
    }
    
    /**
     * Start the real-time feed
     */
    public static function start(): bool
    {
        $status = self::getStatus();
        $status['running'] = true;
        $status['started_at'] = now()->toISOString();
        $status['errors'] = 0;
        
        Cache::put(self::CACHE_KEY_STATUS, $status, now()->addDay());
        
        // Initialize the fetch queue with all active sources
        self::initializeQueue();
        
        Log::info('Real-time feed service started');
        return true;
    }
    
    /**
     * Stop the real-time feed
     */
    public static function stop(): bool
    {
        $status = self::getStatus();
        $status['running'] = false;
        $status['stopped_at'] = now()->toISOString();
        
        Cache::put(self::CACHE_KEY_STATUS, $status, now()->addDay());
        
        Log::info('Real-time feed service stopped');
        return true;
    }
    
    /**
     * Initialize the fetch queue
     */
    public static function initializeQueue(): void
    {
        $sources = NewsSource::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->pluck('id')
            ->toArray();
        
        Cache::put(self::CACHE_KEY_QUEUE, $sources, now()->addDay());
    }
    
    /**
     * Get next source to fetch
     */
    public static function getNextSource(): ?NewsSource
    {
        $queue = Cache::get(self::CACHE_KEY_QUEUE, []);
        
        if (empty($queue)) {
            self::initializeQueue();
            $queue = Cache::get(self::CACHE_KEY_QUEUE, []);
        }
        
        if (empty($queue)) {
            return null;
        }
        
        // Round-robin: get first, move to end
        $sourceId = array_shift($queue);
        $queue[] = $sourceId;
        Cache::put(self::CACHE_KEY_QUEUE, $queue, now()->addDay());
        
        return NewsSource::find($sourceId);
    }
    
    /**
     * Fetch from a single source
     */
    public function fetchFromSource(NewsSource $source): array
    {
        $result = [
            'source' => $source->name,
            'source_id' => $source->id,
            'success' => false,
            'articles_count' => 0,
            'new_articles' => [],
            'error' => null,
            'response_time' => 0,
        ];
        
        $startTime = microtime(true);
        
        try {
            // Update status
            $status = self::getStatus();
            $status['current_source'] = $source->name;
            $status['last_fetch'] = now()->toISOString();
            Cache::put(self::CACHE_KEY_STATUS, $status, now()->addDay());
            
            $feedUrl = $source->rss_feed_url;
            if (empty($feedUrl)) {
                throw new \Exception('No RSS feed URL configured');
            }
            
            // Fetch the RSS feed
            $response = Http::timeout(10)->get($feedUrl);
            
            if (!$response->successful()) {
                throw new \Exception('HTTP error: ' . $response->status());
            }
            
            $content = $response->body();
            $articles = $this->parseRssFeed($content, $source);
            
            $newArticles = [];
            foreach ($articles as $article) {
                // Check if article already exists
                $exists = AggregatedNews::where('original_url', $article['original_url'])->exists();
                
                if (!$exists) {
                    $news = AggregatedNews::create($article);
                    $newArticles[] = [
                        'id' => $news->id,
                        'title' => $news->title,
                        'source' => $source->name,
                        'image' => $news->image_url,
                        'url' => $news->original_url,
                        'published_at' => $news->published_at?->diffForHumans() ?? 'Just now',
                        'is_breaking' => $news->is_breaking,
                    ];
                }
            }
            
            $result['success'] = true;
            $result['articles_count'] = count($articles);
            $result['new_articles'] = $newArticles;
            
            // Update source last_fetched_at
            $source->update(['last_fetched_at' => now()]);
            
            // Update stats
            $this->updateStats($source, count($newArticles), true);
            
            // Add new articles to latest news cache
            if (!empty($newArticles)) {
                $this->addToLatestNews($newArticles);
            }
            
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $this->updateStats($source, 0, false, $e->getMessage());
            Log::warning("Feed fetch failed for {$source->name}: " . $e->getMessage());
        }
        
        $result['response_time'] = round((microtime(true) - $startTime) * 1000);
        
        return $result;
    }
    
    /**
     * Parse RSS feed content
     */
    protected function parseRssFeed(string $content, NewsSource $source): array
    {
        $articles = [];
        
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            
            if ($xml === false) {
                throw new \Exception('Invalid XML');
            }
            
            // Handle different RSS formats
            $items = $xml->channel->item ?? $xml->item ?? $xml->entry ?? [];
            
            foreach ($items as $item) {
                $title = (string)($item->title ?? '');
                $link = (string)($item->link ?? $item->guid ?? '');
                $description = (string)($item->description ?? $item->summary ?? $item->content ?? '');
                $pubDate = (string)($item->pubDate ?? $item->published ?? $item->updated ?? '');
                
                // Handle Atom links
                if (empty($link) && isset($item->link['href'])) {
                    $link = (string)$item->link['href'];
                }
                
                if (empty($title) || empty($link)) {
                    continue;
                }
                
                // Extract image
                $imageUrl = $this->extractImage($item, $description);
                
                // Clean description
                $cleanDescription = strip_tags($description);
                $cleanDescription = html_entity_decode($cleanDescription);
                $cleanDescription = Str::limit($cleanDescription, 500);
                
                // Determine if breaking news (recent and from high-priority source)
                $isBreaking = false;
                if ($source->priority >= 90) {
                    $publishedAt = !empty($pubDate) ? strtotime($pubDate) : time();
                    $isBreaking = (time() - $publishedAt) < 3600; // Within last hour
                }
                
                $articles[] = [
                    'news_source_id' => $source->id,
                    'title' => Str::limit($title, 250),
                    'slug' => Str::slug(Str::limit($title, 100)) . '-' . Str::random(6),
                    'description' => $cleanDescription,
                    'content' => $description,
                    'original_url' => Str::limit($link, 250, ''),
                    'image_url' => $imageUrl ? Str::limit($imageUrl, 250, '') : null,
                    'published_at' => !empty($pubDate) ? date('Y-m-d H:i:s', strtotime($pubDate)) : now(),
                    'fetched_at' => now(),
                    'is_breaking' => $isBreaking,
                    'is_featured' => $source->priority >= 95,
                    'engagement_score' => rand(10, 100),
                    'language' => $source->language ?? 'en',
                    'country' => $source->country ?? 'US',
                ];
            }
        } catch (\Exception $e) {
            Log::warning("RSS parsing error for {$source->name}: " . $e->getMessage());
        }
        
        return $articles;
    }
    
    /**
     * Extract image from RSS item
     */
    protected function extractImage($item, string $description): ?string
    {
        // Check media:content
        $namespaces = $item->getNamespaces(true);
        
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if (isset($media->content)) {
                $attrs = $media->content->attributes();
                if (isset($attrs['url'])) {
                    return (string)$attrs['url'];
                }
            }
            if (isset($media->thumbnail)) {
                $attrs = $media->thumbnail->attributes();
                if (isset($attrs['url'])) {
                    return (string)$attrs['url'];
                }
            }
        }
        
        // Check enclosure
        if (isset($item->enclosure)) {
            $attrs = $item->enclosure->attributes();
            if (isset($attrs['url']) && strpos((string)$attrs['type'], 'image') !== false) {
                return (string)$attrs['url'];
            }
        }
        
        // Extract from description
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $description, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Update statistics
     */
    protected function updateStats(NewsSource $source, int $articlesCount, bool $success, ?string $error = null): void
    {
        $stats = Cache::get(self::CACHE_KEY_STATS, [
            'total_fetched_today' => 0,
            'fetched_last_hour' => 0,
            'fetched_last_minute' => 0,
            'sources_active' => 0,
            'sources_failed' => [],
            'fetch_rate' => 0,
            'response_times' => [],
        ]);
        
        if ($success) {
            $stats['total_fetched_today'] += $articlesCount;
            $stats['fetched_last_minute'] = ($stats['fetched_last_minute'] ?? 0) + $articlesCount;
            $stats['sources_active'] = ($stats['sources_active'] ?? 0) + 1;
            
            // Remove from failed if was there
            unset($stats['sources_failed'][$source->id]);
        } else {
            $stats['sources_failed'][$source->id] = [
                'name' => $source->name,
                'error' => $error,
                'time' => now()->toISOString(),
            ];
        }
        
        // Update status
        $status = self::getStatus();
        $status['sources_processed'] = ($status['sources_processed'] ?? 0) + 1;
        $status['articles_fetched'] = ($status['articles_fetched'] ?? 0) + $articlesCount;
        if (!$success) {
            $status['errors'] = ($status['errors'] ?? 0) + 1;
        }
        Cache::put(self::CACHE_KEY_STATUS, $status, now()->addDay());
        
        Cache::put(self::CACHE_KEY_STATS, $stats, now()->addDay());
    }
    
    /**
     * Add articles to latest news cache
     */
    protected function addToLatestNews(array $articles): void
    {
        $latest = Cache::get(self::CACHE_KEY_LATEST, []);
        
        // Prepend new articles
        $latest = array_merge($articles, $latest);
        
        // Keep only last 50
        $latest = array_slice($latest, 0, 50);
        
        Cache::put(self::CACHE_KEY_LATEST, $latest, now()->addHour());
    }
    
    /**
     * Perform one fetch cycle
     */
    public function cycle(): array
    {
        $status = self::getStatus();
        
        if (!$status['running']) {
            return ['running' => false, 'message' => 'Feed service is not running'];
        }
        
        $source = self::getNextSource();
        
        if (!$source) {
            return ['running' => true, 'message' => 'No sources available'];
        }
        
        return $this->fetchFromSource($source);
    }
}
