<?php

namespace App\Console\Commands;

use App\Models\Analytics;
use App\Models\TrendingTopic;
use App\Services\AnalyticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateAnalyticsReport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:generate 
                            {--period=day : Period for the report (day, week, month)}
                            {--email= : Send report to email}';

    /**
     * The console command description.
     */
    protected $description = 'Generate analytics reports and cache them for dashboard';

    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        parent::__construct();
        $this->analyticsService = $analyticsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $this->info("Generating analytics report for: {$period}");
        $this->newLine();

        try {
            // Generate dashboard analytics
            $this->info('Calculating overview statistics...');
            $analytics = $this->analyticsService->getDashboardAnalytics($period);

            // Cache the results
            Cache::put("analytics.dashboard.{$period}", $analytics, now()->addHours(1));
            $this->info('Dashboard analytics cached.');

            // Display summary
            $this->newLine();
            $this->table(['Metric', 'Value'], [
                ['Total Views', number_format($analytics['overview']['total_views']['value'] ?? 0)],
                ['Unique Visitors', number_format($analytics['overview']['unique_visitors']['value'] ?? 0)],
                ['Avg Session Duration', $analytics['overview']['avg_session_duration']['formatted'] ?? '0s'],
                ['Bounce Rate', ($analytics['overview']['bounce_rate']['value'] ?? 0) . '%'],
                ['Countries', $analytics['demographics']['countries_count'] ?? 0],
                ['Trending Topics', count($analytics['trending']['trending_topics'] ?? [])],
            ]);

            // Generate trending topics
            $this->newLine();
            $this->info('Updating trending topics...');
            $this->updateTrendingScores();
            $this->info('Trending topics updated.');

            // Clean old analytics data (older than 90 days)
            $this->info('Cleaning old analytics data...');
            $deleted = Analytics::where('created_at', '<', now()->subDays(90))->delete();
            $this->info("Deleted {$deleted} old analytics records.");

            // Send email report if specified
            if ($email = $this->option('email')) {
                $this->info("Sending report to: {$email}");
                // TODO: Implement email sending
            }

            $this->newLine();
            $this->info('Analytics report generated successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error generating report: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Update trending topic scores
     */
    protected function updateTrendingScores()
    {
        $topics = TrendingTopic::where('is_active', true)->get();

        foreach ($topics as $topic) {
            // Calculate velocity (rate of engagement change)
            $oldScore = $topic->engagement_score;
            
            // Get recent engagement for this topic
            $recentEngagement = Analytics::where('created_at', '>=', now()->subHours(6))
                ->whereJsonContains('metadata->topic', $topic->topic)
                ->count();

            // Update velocity
            $velocity = $recentEngagement - ($oldScore / 10);
            $topic->trend_velocity = $velocity;
            
            // Decay score slightly for topics that aren't getting engagement
            if ($recentEngagement < 10) {
                $topic->engagement_score = max(0, $topic->engagement_score * 0.95);
            }

            // Deactivate topics with very low scores
            if ($topic->engagement_score < 10 && $topic->created_at < now()->subDays(2)) {
                $topic->is_active = false;
            }

            $topic->save();
        }
    }
}
