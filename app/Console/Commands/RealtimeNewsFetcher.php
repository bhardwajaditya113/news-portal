<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RealtimeNewsFetcher extends Command
{
    protected $signature = 'news:realtime 
                            {--interval=10 : Interval in seconds between fetch cycles}
                            {--daemon : Run as daemon (continuous loop)}';
    
    protected $description = 'Fetch news in real-time with configurable intervals';

    protected $aggregatorService;
    protected $running = true;

    public function __construct(NewsAggregatorService $aggregatorService)
    {
        parent::__construct();
        $this->aggregatorService = $aggregatorService;
    }

    public function handle()
    {
        $interval = (int) $this->option('interval');
        $isDaemon = $this->option('daemon');

        // Handle signals for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function () {
                $this->running = false;
                $this->info("\nShutting down gracefully...");
            });
            pcntl_signal(SIGINT, function () {
                $this->running = false;
                $this->info("\nShutting down gracefully...");
            });
        }

        $this->info("Starting real-time news fetcher (interval: {$interval}s)");
        $this->info("Press Ctrl+C to stop\n");

        do {
            $startTime = microtime(true);
            
            try {
                $this->fetchCycle();
            } catch (\Exception $e) {
                $this->error("Error in fetch cycle: " . $e->getMessage());
                Log::error("Realtime fetcher error: " . $e->getMessage());
            }

            $elapsed = microtime(true) - $startTime;
            $sleepTime = max(0, $interval - $elapsed);

            if ($isDaemon && $this->running) {
                // Show status
                $this->line(sprintf(
                    "[%s] Cycle completed in %.2fs, sleeping %.2fs",
                    now()->format('H:i:s'),
                    $elapsed,
                    $sleepTime
                ));
                
                // Sleep in 1-second intervals to allow signal handling
                for ($i = 0; $i < $sleepTime && $this->running; $i++) {
                    sleep(1);
                    if (function_exists('pcntl_signal_dispatch')) {
                        pcntl_signal_dispatch();
                    }
                }
            }
        } while ($isDaemon && $this->running);

        $this->info("Fetcher stopped.");
        return Command::SUCCESS;
    }

    protected function fetchCycle()
    {
        // Get sources that need to be fetched (based on their individual intervals)
        $sources = NewsSource::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_fetched_at')
                    ->orWhereRaw('TIMESTAMPDIFF(MINUTE, last_fetched_at, NOW()) >= fetch_interval');
            })
            ->orderBy('priority', 'desc')
            ->get();

        if ($sources->isEmpty()) {
            $this->line("No sources need fetching right now.");
            return;
        }

        $this->info("Fetching from " . $sources->count() . " source(s)...");

        $totalFetched = 0;
        foreach ($sources as $source) {
            try {
                $result = $this->aggregatorService->fetchFromSource($source->id);
                $fetched = $result['fetched'] ?? 0;
                $totalFetched += $fetched;
                
                if ($fetched > 0) {
                    $this->info("  ✓ {$source->name}: {$fetched} new articles");
                }
            } catch (\Exception $e) {
                $this->warn("  ✗ {$source->name}: " . $e->getMessage());
            }
        }

        if ($totalFetched > 0) {
            $this->info("Total: {$totalFetched} new articles fetched\n");
        }
    }
}
