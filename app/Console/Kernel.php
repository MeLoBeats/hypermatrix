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
        // Door locks sync daily at 03:00.
        $schedule->command('doorlock:sync')
                 ->dailyAt('3:00')
                 ->withoutOverlapping()
                 ->onOneServer();

        // Hyperplanning course sync twice daily.
        $schedule->command('hyperplanning:sync')
                 ->twiceDaily(6, 18)
                 ->withoutOverlapping()
                 ->onOneServer();
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
