<?php

namespace App\Console\Commands;

use App\Services\RealtimeFeedService;
use Illuminate\Console\Command;

class ContinuousFeedFetcher extends Command
{
    protected $signature = 'feed:continuous {--interval=5 : Seconds between fetches} {--duration=60 : How long to run in seconds}';
    protected $description = 'Continuously fetch news from sources for a specified duration';

    public function handle()
    {
        $interval = (int) $this->option('interval');
        $duration = (int) $this->option('duration');
        $endTime = time() + $duration;
        
        $service = new RealtimeFeedService();
        
        // Auto-start the service
        RealtimeFeedService::start();
        
        $this->info("Starting continuous feed fetcher (interval: {$interval}s, duration: {$duration}s)");
        $this->newLine();
        
        $fetchCount = 0;
        $articleCount = 0;
        $errorCount = 0;
        
        while (time() < $endTime) {
            $result = $service->cycle();
            $fetchCount++;
            
            if (isset($result['success'])) {
                if ($result['success']) {
                    $newCount = count($result['new_articles'] ?? []);
                    $articleCount += $newCount;
                    
                    $status = $newCount > 0 ? '<fg=green>✓</>' : '<fg=yellow>○</>';
                    $this->line(sprintf(
                        "%s [%s] %s: %d articles (%d new) - %dms",
                        $status,
                        now()->format('H:i:s'),
                        $result['source'] ?? 'Unknown',
                        $result['articles_count'] ?? 0,
                        $newCount,
                        $result['response_time'] ?? 0
                    ));
                } else {
                    $errorCount++;
                    $this->line(sprintf(
                        "<fg=red>✗</> [%s] %s: %s",
                        now()->format('H:i:s'),
                        $result['source'] ?? 'Unknown',
                        $result['error'] ?? 'Unknown error'
                    ));
                }
            }
            
            sleep($interval);
        }
        
        $this->newLine();
        $this->info("Completed: {$fetchCount} fetches, {$articleCount} new articles, {$errorCount} errors");
        
        return Command::SUCCESS;
    }
}
