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
        // Every minute: alert workers whose service starts within the next 5 minutes
        $schedule->command('notify:upcoming-services')->everyMinute();

        // Every minute: alert providers about bookings starting in 20 minutes missing a worker
        $schedule->command('notify:unassigned-services')->everyMinute();
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
