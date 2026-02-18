<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchAggregatedNews extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'news:fetch 
                            {--source= : Fetch from specific source ID}
                            {--all : Fetch from all active sources}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch news from configured RSS/API sources';

    protected $aggregatorService;

    public function __construct(NewsAggregatorService $aggregatorService)
    {
        parent::__construct();
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting news aggregation...');
        $this->newLine();

        try {
            if ($sourceId = $this->option('source')) {
                // Fetch from specific source
                $source = NewsSource::findOrFail($sourceId);
                $this->info("Fetching from: {$source->name}");
                
                $items = $this->aggregatorService->fetchFromSource($source);
                $source->update(['last_fetched_at' => now()]);
                
                $this->info("Fetched " . count($items) . " new articles from {$source->name}");
            } else {
                // Fetch from all sources
                $this->info('Fetching from all active sources...');
                
                $sources = NewsSource::active()->byPriority()->get();
                $table = [];
                $totalFetched = 0;
                $totalErrors = 0;

                foreach ($sources as $source) {
                    try {
                        $items = $this->aggregatorService->fetchFromSource($source);
                        $count = count($items);
                        $source->update(['last_fetched_at' => now()]);
                        
                        $table[] = [
                            $source->name,
                            $count,
                            'success',
                            '-'
                        ];
                        $totalFetched += $count;
                    } catch (\Exception $e) {
                        $table[] = [
                            $source->name,
                            0,
                            'error',
                            \Str::limit($e->getMessage(), 40)
                        ];
                        $totalErrors++;
                    }
                }

                $this->table(['Source', 'Fetched', 'Status', 'Error'], $table);
                $this->newLine();
                $this->info("Total: {$totalFetched} articles fetched from " . count($sources) . " sources");
                
                if ($totalErrors > 0) {
                    $this->warn("{$totalErrors} source(s) had errors");
                }
            }

            // Update trending topics
            $this->info('Updating trending topics...');
            $this->aggregatorService->updateTrendingTopics();
            $this->info('Trending topics updated!');

            $this->newLine();
            $this->info('News aggregation completed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error during aggregation: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
