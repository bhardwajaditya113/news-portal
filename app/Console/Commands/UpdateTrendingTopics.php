<?php

namespace App\Console\Commands;

use App\Models\AggregatedNews;
use App\Models\TrendingTopic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTrendingTopics extends Command
{
    protected $signature = 'news:update-trending';
    protected $description = 'Update trending topics based on recent news engagement';

    public function handle()
    {
        $this->info('Updating trending topics...');

        // Extract keywords from recent news titles
        $recentNews = AggregatedNews::where('created_at', '>=', now()->subHours(24))
            ->where('status', 'published')
            ->get();

        $keywords = [];
        
        foreach ($recentNews as $news) {
            // Extract words from title
            $words = $this->extractKeywords($news->title);
            foreach ($words as $word) {
                if (!isset($keywords[$word])) {
                    $keywords[$word] = ['count' => 0, 'engagement' => 0, 'news_ids' => []];
                }
                $keywords[$word]['count']++;
                $keywords[$word]['engagement'] += $news->engagement_score ?? 1;
                $keywords[$word]['news_ids'][] = $news->id;
            }

            // Also use existing keywords
            if ($news->keywords) {
                foreach ($news->keywords as $keyword) {
                    $kw = strtolower($keyword);
                    if (!isset($keywords[$kw])) {
                        $keywords[$kw] = ['count' => 0, 'engagement' => 0, 'news_ids' => []];
                    }
                    $keywords[$kw]['count']++;
                    $keywords[$kw]['engagement'] += $news->engagement_score ?? 1;
                    $keywords[$kw]['news_ids'][] = $news->id;
                }
            }
        }

        // Filter and sort
        $trending = collect($keywords)
            ->filter(fn($data) => $data['count'] >= 3) // Minimum 3 articles
            ->sortByDesc('engagement')
            ->take(50);

        // Update or create trending topics
        foreach ($trending as $topic => $data) {
            $existing = TrendingTopic::where('topic', $topic)->first();
            $oldScore = $existing ? $existing->engagement_score : 0;
            
            TrendingTopic::updateOrCreate(
                ['topic' => $topic],
                [
                    'news_count' => $data['count'],
                    'engagement_score' => $data['engagement'],
                    'trend_velocity' => $data['engagement'] - $oldScore,
                    'related_news_ids' => array_unique(array_slice($data['news_ids'], 0, 10)),
                    'is_active' => true,
                    'last_updated_at' => now(),
                ]
            );
        }

        // Deactivate old topics
        TrendingTopic::where('last_updated_at', '<', now()->subDays(2))
            ->update(['is_active' => false]);

        $this->info('Updated ' . $trending->count() . ' trending topics');
        return Command::SUCCESS;
    }

    protected function extractKeywords($text)
    {
        // Common stop words to exclude
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
                      'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
                      'has', 'have', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
                      'should', 'may', 'might', 'must', 'shall', 'can', 'need', 'dare',
                      'it', 'its', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 
                      'she', 'we', 'they', 'what', 'which', 'who', 'whom', 'whose',
                      'where', 'when', 'why', 'how', 'all', 'each', 'every', 'both',
                      'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not',
                      'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'also',
                      'now', 'new', 'says', 'said', 'after', 'before', 'over', 'under',
                      'again', 'further', 'then', 'once', 'here', 'there', 'any', 'about'];

        $words = preg_split('/[\s,.\-:;!?\'"()]+/', strtolower($text));
        
        return array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords) && !is_numeric($word);
        });
    }
}
