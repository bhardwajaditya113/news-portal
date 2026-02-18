<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Continuous real-time feed fetcher - runs for 55 seconds every minute
        // This ensures near-real-time news updates (fetches every 3 seconds)
        $schedule->command('feed:continuous --interval=3 --duration=55')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/realtime-feed.log'));

        // Fetch news every minute for near real-time updates
        // The command will check each source's individual fetch_interval
        $schedule->command('news:fetch --all')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/news-aggregation.log'));

        // Update trending topics every 2 minutes
        $schedule->command('news:update-trending')
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/trending.log'));

        // Generate analytics reports every 5 minutes for real-time dashboard
        $schedule->command('analytics:generate --period=day')
            ->everyFiveMinutes()
            ->appendOutputTo(storage_path('logs/analytics.log'));

        // Generate weekly report every Monday at 8am
        $schedule->command('analytics:generate --period=week')
            ->weeklyOn(1, '8:00')
            ->appendOutputTo(storage_path('logs/analytics.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
