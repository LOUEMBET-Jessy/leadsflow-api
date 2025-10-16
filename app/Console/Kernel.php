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
        // Check for overdue tasks every hour
        $schedule->job(new \App\Jobs\CheckOverdueTasks::class)
            ->hourly()
            ->withoutOverlapping();

        // Generate weekly reports every Monday at 9 AM
        $schedule->job(new \App\Jobs\GenerateWeeklyReport::class)
            ->weekly()
            ->mondays()
            ->at('09:00')
            ->withoutOverlapping();

        // Sync external data every 4 hours
        $schedule->call(function () {
            $integrations = \App\Models\Integration::where('status', 'connected')->get();
            foreach ($integrations as $integration) {
                \App\Jobs\SyncExternalData::dispatch($integration, 'all');
            }
        })->everyFourHours();

        // Clean up old notifications (older than 30 days)
        $schedule->call(function () {
            \App\Models\Notification::where('created_at', '<', now()->subDays(30))->delete();
        })->daily();

        // Clean up old logs (older than 7 days)
        $schedule->call(function () {
            \Illuminate\Support\Facades\Log::channel('daily')->info('Cleaning up old logs');
        })->daily();

        // Process automations every 15 minutes
        $schedule->command('automations:process')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        // Generate AI insights every hour
        $schedule->command('ai:generate-insights')
            ->hourly()
            ->withoutOverlapping();

        // Sync integrations every 2 hours
        $schedule->command('integrations:sync')
            ->everyTwoHours()
            ->withoutOverlapping();

        // Clean up old data weekly
        $schedule->command('data:cleanup')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->withoutOverlapping();

        // Generate weekly reports every Monday at 8 AM
        $schedule->command('reports:generate --type=weekly')
            ->weekly()
            ->mondays()
            ->at('08:00')
            ->withoutOverlapping();

        // Generate monthly reports on the 1st of each month at 8 AM
        $schedule->command('reports:generate --type=monthly')
            ->monthly()
            ->at('08:00')
            ->withoutOverlapping();
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
